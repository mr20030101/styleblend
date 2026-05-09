<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'customer'])
            ->orderByDesc('created_at');

        if ($request->search) {
            $query->where('transaction_number', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'data' => $transactions->map(fn($t) => [
                'id'                 => $t->id,
                'transaction_number' => $t->transaction_number,
                'cashier'            => $t->user->name,
                'customer'           => $t->customer?->name ?? 'Walk-in',
                'subtotal'           => $t->subtotal,
                'discount'           => $t->discount,
                'tax'                => $t->tax,
                'total'              => $t->total,
                'amount_paid'        => $t->amount_paid,
                'change'             => $t->change_amount,
                'status'             => $t->status,
                'created_at'         => $t->created_at->toISOString(),
            ]),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
                'per_page'     => $transactions->perPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['items', 'user', 'customer'])->findOrFail($id);

        return response()->json([
            'id'                 => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'cashier'            => $transaction->user->name,
            'customer'           => $transaction->customer?->name ?? 'Walk-in',
            'subtotal'           => $transaction->subtotal,
            'discount'           => $transaction->discount,
            'tax'                => $transaction->tax,
            'total'              => $transaction->total,
            'amount_paid'        => $transaction->amount_paid,
            'change'             => $transaction->change_amount,
            'status'             => $transaction->status,
            'void_reason'        => $transaction->void_reason,
            'created_at'         => $transaction->created_at->toISOString(),
            'items'              => $transaction->items->map(fn($i) => [
                'product_name' => $i->product_name,
                'variant_info' => $i->variant_info,
                'quantity'     => $i->quantity,
                'unit_price'   => $i->unit_price,
                'subtotal'     => $i->subtotal,
            ]),
        ]);
    }
}
