<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductVariant::with('product.category')
            ->whereHas('product', fn($q) => $q->where('is_active', true));

        if ($request->search) {
            $query->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }
        if ($request->category_id) {
            $query->whereHas('product', fn($q) => $q->where('category_id', $request->category_id));
        }
        if ($request->low_stock) {
            $query->where('stock_quantity', '<=', 5);
        }

        $variants = $query->orderBy('stock_quantity')->paginate(20)->withQueryString();
        $categories = \App\Models\Category::where('is_active', true)->get();
        $lowStockCount = ProductVariant::where('stock_quantity', '<=', 5)->count();

        return view('inventory.index', compact('variants', 'categories', 'lowStockCount'));
    }

    public function adjust(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'type' => 'required|in:restock,adjustment,return',
            'quantity' => 'required|integer|not_in:0',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $variant) {
            $stockBefore = $variant->stock_quantity;
            $newStock = $stockBefore + $data['quantity'];

            if ($newStock < 0) {
                throw new \Exception('Stock cannot go below zero.');
            }

            $variant->update(['stock_quantity' => $newStock]);

            Inventory::create([
                'product_variant_id' => $variant->id,
                'type' => $data['type'],
                'quantity' => $data['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'notes' => $data['notes'] ?? null,
                'user_id' => Auth::id(),
            ]);
        });

        return response()->json(['success' => true, 'new_stock' => $variant->fresh()->stock_quantity]);
    }

    public function history(ProductVariant $variant)
    {
        $logs = Inventory::with('user')
            ->where('product_variant_id', $variant->id)
            ->orderBy('created_at', 'desc')
            ->take(50)->get();
        return response()->json($logs);
    }
}
