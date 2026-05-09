<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RaffleEntry;
use App\Models\RafflePeriod;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $taxEnabled = \App\Models\Setting::get('tax_enabled', '1') == '1';
        $taxRate    = (float) \App\Models\Setting::get('tax_rate', '12');
        $taxLabel   = \App\Models\Setting::get('tax_label', 'VAT');
        $taxInclusive = \App\Models\Setting::get('tax_inclusive', '0') == '1';
        $discountEnabled = \App\Models\Setting::get('discount_enabled', '1') == '1';
        $maxDiscount = (float) \App\Models\Setting::get('max_discount', '0');
        $currencySymbol = \App\Models\Setting::get('currency_symbol', '₱');
        $activeRaffle = \App\Models\RafflePeriod::getActive() !== null;
        return view('pos.index', compact(
            'categories', 'taxEnabled', 'taxRate', 'taxLabel',
            'taxInclusive', 'discountEnabled', 'maxDiscount', 'currencySymbol', 'activeRaffle'
        ));
    }

    public function searchProducts(Request $request)
    {
        $query = Product::with(['category', 'activeVariants'])
            ->where('is_active', true);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%')
                  ->orWhere('barcode', $search)
                  ->orWhereHas('variants', fn($vq) => $vq->where('sku', 'like', '%' . $search . '%'));
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Only show products that have at least one active variant (any stock level)
        $query->whereHas('activeVariants');

        $products = $query->orderBy('name')->take(50)->get();

        return response()->json($products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'image_url' => $p->image_url,
                'category' => $p->category->name,
                'variants' => $p->activeVariants->map(fn($v) => [
                    'id' => $v->id,
                    'size' => $v->size,
                    'color' => $v->color,
                    'price' => $v->price,
                    'stock_quantity' => $v->stock_quantity,
                    'variant_info' => $v->variant_info,
                ]),
            ];
        }));
    }

    public function scanBarcode(Request $request)
    {
        $code = trim($request->barcode ?? '');
        if (!$code) return response()->json(['found' => false]);

        // 1. Exact match on variant SKU (printed on barcode labels)
        $variant = ProductVariant::with(['product.category'])
            ->where('sku', $code)
            ->where('is_active', true)
            ->first();

        if ($variant && $variant->product->is_active) {
            return response()->json([
                'found'   => true,
                'type'    => 'variant',
                'product' => [
                    'id'        => $variant->product->id,
                    'name'      => $variant->product->name,
                    'image_url' => $variant->product->image_url,
                    'category'  => $variant->product->category->name,
                    'variants'  => $variant->product->activeVariants->map(fn($v) => [
                        'id'             => $v->id,
                        'size'           => $v->size,
                        'color'          => $v->color,
                        'price'          => $v->price,
                        'stock_quantity' => $v->stock_quantity,
                        'variant_info'   => $v->variant_info,
                    ]),
                ],
                'variant' => [
                    'id'             => $variant->id,
                    'size'           => $variant->size,
                    'color'          => $variant->color,
                    'price'          => $variant->price,
                    'stock_quantity' => $variant->stock_quantity,
                    'variant_info'   => $variant->variant_info,
                ],
            ]);
        }

        // 2. Exact match on product barcode or product SKU
        $product = Product::with(['activeVariants', 'category'])
            ->where('is_active', true)
            ->where(fn($q) => $q->where('barcode', $code)->orWhere('sku', $code))
            ->first();

        if ($product) {
            $variants = $product->activeVariants;
            $data = [
                'found'   => true,
                'type'    => $variants->count() === 1 ? 'variant' : 'product',
                'product' => [
                    'id'        => $product->id,
                    'name'      => $product->name,
                    'image_url' => $product->image_url,
                    'category'  => $product->category->name,
                    'variants'  => $variants->map(fn($v) => [
                        'id'             => $v->id,
                        'size'           => $v->size,
                        'color'          => $v->color,
                        'price'          => $v->price,
                        'stock_quantity' => $v->stock_quantity,
                        'variant_info'   => $v->variant_info,
                    ]),
                ],
            ];
            if ($variants->count() === 1) {
                $v = $variants->first();
                $data['variant'] = [
                    'id'             => $v->id,
                    'size'           => $v->size,
                    'color'          => $v->color,
                    'price'          => $v->price,
                    'stock_quantity' => $v->stock_quantity,
                    'variant_info'   => $v->variant_info,
                ];
            }
            return response()->json($data);
        }

        return response()->json(['found' => false]);
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items'       => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'discount'    => 'nullable|numeric|min:0',
            'tax'         => 'nullable|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name'  => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $variant = ProductVariant::with('product')->lockForUpdate()->find($item['variant_id']);

                if ($variant->stock_quantity < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$variant->product->name} ({$variant->variant_info}). Available: {$variant->stock_quantity}"
                    ], 422);
                }

                $lineTotal = $variant->price * $item['quantity'];
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'unit_price' => $variant->price,
                    'subtotal' => $lineTotal,
                ];
            }

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $discount + $tax;
            $amountPaid = $data['amount_paid'];

            if ($amountPaid < $total) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Insufficient payment amount.'], 422);
            }

            $transaction = Transaction::create([
                'transaction_number' => Transaction::generateNumber(),
                'user_id'       => Auth::id(),
                'customer_id'   => $data['customer_id'] ?? null,
                'subtotal'      => $subtotal,
                'discount'      => $discount,
                'tax'           => $tax,
                'total'         => $total,
                'amount_paid'   => $amountPaid,
                'change_amount' => $amountPaid - $total,
                'payment_method' => 'cash',
                'status'        => 'completed',
            ]);

            foreach ($itemsData as $item) {
                $variant = $item['variant'];
                $stockBefore = $variant->stock_quantity;
                $stockAfter = $stockBefore - $item['quantity'];

                $variant->update(['stock_quantity' => $stockAfter]);

                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => 'Sale: ' . $transaction->transaction_number,
                    'user_id' => Auth::id(),
                ]);

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'variant_info' => $variant->variant_info,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();

            // Generate raffle entries
            $raffleEntries = 0;
            $perEntry = (float) \App\Models\Setting::get('raffle_per_entry', 300);
            $raffleEnabled = \App\Models\Setting::get('raffle_enabled', '1') == '1';
            $customerName  = null;
            $customerPhone = null;

            if ($raffleEnabled && $perEntry > 0) {
                $activePeriod  = RafflePeriod::getActive();
                $entriesEarned = $activePeriod ? RaffleEntry::calcEntries($total, $perEntry) : 0;

                if ($entriesEarned > 0) {
                    if (!empty($data['customer_id'])) {
                        $customer = Customer::find($data['customer_id']);
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
                'success'            => true,
                'transaction_id'     => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'change'             => $transaction->change_amount,
                'raffle_entries'     => $raffleEntries,
                'raffle_tickets'     => $raffleEntries > 0 ? RaffleEntry::where('transaction_id', $transaction->id)->first()?->ticket_numbers : null,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function receipt($id)
    {
        $transaction = Transaction::with(['items', 'user'])->findOrFail($id);
        $storeName    = \App\Models\Setting::get('store_name', 'Clothing Store POS');
        $storeAddress = \App\Models\Setting::get('store_address', '');
        $storePhone   = \App\Models\Setting::get('store_phone', '');
        $storeFooter  = \App\Models\Setting::get('store_footer', 'Thank you for shopping with us!');
        $taxLabel     = \App\Models\Setting::get('tax_label', 'VAT');
        $storeLogo    = \App\Models\Setting::get('store_logo', '');
        return view('pos.receipt', compact('transaction', 'storeName', 'storeAddress', 'storePhone', 'storeFooter', 'taxLabel', 'storeLogo'));
    }
}
