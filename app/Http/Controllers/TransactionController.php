<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'customer'])
            ->orderBy('created_at', 'desc');

        if ($request->search) {
            $query->where('transaction_number', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->paginate(20)->withQueryString();
        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.productVariant.product', 'user', 'customer', 'voidedBy']);
        return view('transactions.show', compact('transaction'));
    }

    public function void(Request $request, Transaction $transaction)
    {
        if ($transaction->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Transaction already voided.'], 422);
        }

        $data = $request->validate(['reason' => 'required|string|max:255']);

        DB::transaction(function () use ($transaction, $data) {
            // Restock all items (skip custom items — no variant, no stock)
            foreach ($transaction->items as $item) {
                $variant = $item->productVariant;
                if (!$variant) continue;

                $stockBefore = $variant->stock_quantity;
                $stockAfter  = $stockBefore + $item->quantity;

                $variant->update(['stock_quantity' => $stockAfter]);

                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'type'         => 'return',
                    'quantity'     => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'notes'        => 'Void: ' . $transaction->transaction_number . ' — ' . $data['reason'],
                    'user_id'      => Auth::id(),
                ]);
            }

            $transaction->update([
                'status'     => 'voided',
                'void_reason' => $data['reason'],
                'voided_by'  => Auth::id(),
                'voided_at'  => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Transaction voided and stock restored.']);
    }
}
