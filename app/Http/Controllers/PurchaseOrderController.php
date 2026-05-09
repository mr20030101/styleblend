<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier')->orderBy('created_at', 'desc')->paginate(20);
        return view('purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products  = Product::with('activeVariants')->where('is_active', true)->orderBy('name')->get();

        // Normalize for JS — map activeVariants to snake_case key
        $productsJson = $products->map(fn($p) => [
            'id'   => $p->id,
            'name' => $p->name,
            'active_variants' => $p->activeVariants->map(fn($v) => [
                'id'         => $v->id,
                'size'       => $v->size,
                'color'      => $v->color,
                'price'      => $v->price,
                'cost_price' => $v->cost_price,
            ]),
        ]);

        return view('purchase_orders.create', compact('suppliers', 'products', 'productsJson'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date'  => 'required|date',
            'notes'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.variant_id'  => 'required|exists:product_variants,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.cost_price'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $total = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['cost_price']);

            $po = PurchaseOrder::create([
                'po_number'   => PurchaseOrder::generateNumber(),
                'supplier_id' => $data['supplier_id'],
                'user_id'     => Auth::id(),
                'order_date'  => $data['order_date'],
                'notes'       => $data['notes'] ?? null,
                'total_cost'  => $total,
                'status'      => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id'  => $po->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity'           => $item['quantity'],
                    'cost_price'         => $item['cost_price'],
                    'subtotal'           => $item['quantity'] * $item['cost_price'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.productVariant.product', 'user']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Order already processed.'], 422);
        }

        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                $variant     = $item->productVariant;
                $stockBefore = $variant->stock_quantity;
                $stockAfter  = $stockBefore + $item->quantity;

                $variant->update([
                    'stock_quantity' => $stockAfter,
                    'cost_price'     => $item->cost_price,
                ]);

                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'type'        => 'restock',
                    'quantity'    => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'notes'       => 'PO: ' . $purchaseOrder->po_number,
                    'user_id'     => Auth::id(),
                ]);
            }

            $purchaseOrder->update([
                'status'        => 'received',
                'received_date' => now()->toDateString(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Stock updated successfully.']);
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Cannot cancel a processed order.'], 422);
        }
        $purchaseOrder->update(['status' => 'cancelled']);
        return response()->json(['success' => true]);
    }
}
