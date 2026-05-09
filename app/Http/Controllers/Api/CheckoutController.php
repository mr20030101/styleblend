<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\RaffleEntry;
use App\Models\RafflePeriod;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'discount'           => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'amount_paid'        => 'required|numeric|min:0',
            'customer_id'        => 'nullable|exists:customers,id',
            'customer_name'      => 'nullable|string|max:100',
            'customer_phone'     => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $subtotal  = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::with('product')->lockForUpdate()->find($item['variant_id']);

                if ($variant->stock_quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "Insufficient stock for {$variant->product->name} ({$variant->variant_info}). Available: {$variant->stock_quantity}"
                    ], 422);
                }

                $lineTotal  = $variant->price * $item['quantity'];
                $subtotal  += $lineTotal;
                $itemsData[] = [
                    'variant'    => $variant,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $variant->price,
                    'subtotal'   => $lineTotal,
                ];
            }

            $discount   = $data['discount'] ?? 0;
            $tax        = $data['tax'] ?? 0;
            $total      = $subtotal - $discount + $tax;
            $amountPaid = $data['amount_paid'];

            if ($amountPaid < $total) {
                DB::rollBack();
                return response()->json(['message' => 'Insufficient payment amount.'], 422);
            }

            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateNumber(),
                'user_id'            => Auth::id(),
                'customer_id'        => $data['customer_id'] ?? null,
                'subtotal'           => $subtotal,
                'discount'           => $discount,
                'tax'                => $tax,
                'total'              => $total,
                'amount_paid'        => $amountPaid,
                'change_amount'      => $amountPaid - $total,
                'payment_method'     => 'cash',
                'status'             => 'completed',
            ]);

            foreach ($itemsData as $item) {
                $variant     = $item['variant'];
                $stockBefore = $variant->stock_quantity;
                $stockAfter  = $stockBefore - $item['quantity'];

                $variant->update(['stock_quantity' => $stockAfter]);

                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'type'               => 'sale',
                    'quantity'           => -$item['quantity'],
                    'stock_before'       => $stockBefore,
                    'stock_after'        => $stockAfter,
                    'notes'              => 'Sale: ' . $transaction->transaction_number,
                    'user_id'            => Auth::id(),
                ]);

                TransactionItem::create([
                    'transaction_id'     => $transaction->id,
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name,
                    'variant_info'       => $variant->variant_info,
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $item['unit_price'],
                    'subtotal'           => $item['subtotal'],
                ]);
            }

            DB::commit();

            // Raffle entries
            $raffleEntries = 0;
            $perEntry      = (float) \App\Models\Setting::get('raffle_per_entry', 300);
            $raffleEnabled = \App\Models\Setting::get('raffle_enabled', '1') == '1';

            if ($raffleEnabled && $perEntry > 0) {
                $activePeriod  = RafflePeriod::getActive();
                $entriesEarned = $activePeriod ? RaffleEntry::calcEntries($total, $perEntry) : 0;

                if ($entriesEarned > 0) {
                    $customerName  = null;
                    $customerPhone = null;

                    if (!empty($data['customer_id'])) {
                        $customer      = Customer::find($data['customer_id']);
                        $customerName  = $customer?->name;
                        $customerPhone = $customer?->phone;
                    } else {
                        $customerName  = $data['customer_name'] ?? null;
                        $customerPhone = $data['customer_phone'] ?? null;
                    }

                    if ($customerName) {
                        $tickets = RaffleEntry::generateTickets($entriesEarned, $activePeriod->id);
                        RaffleEntry::create([
                            'raffle_period_id' => $activePeriod->id,
                            'customer_id'      => $data['customer_id'] ?? null,
                            'customer_name'    => $customerName,
                            'customer_phone'   => $customerPhone,
                            'transaction_id'   => $transaction->id,
                            'entries'          => $entriesEarned,
                            'ticket_numbers'   => implode(',', $tickets),
                        ]);
                        $raffleEntries = $entriesEarned;
                    }
                }
            }

            return response()->json([
                'transaction_id'     => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'subtotal'           => $transaction->subtotal,
                'discount'           => $transaction->discount,
                'tax'                => $transaction->tax,
                'total'              => $transaction->total,
                'amount_paid'        => $transaction->amount_paid,
                'change'             => $transaction->change_amount,
                'raffle_entries'     => $raffleEntries,
                'raffle_tickets'     => $raffleEntries > 0
                    ? RaffleEntry::where('transaction_id', $transaction->id)->first()?->ticket_numbers
                    : null,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
