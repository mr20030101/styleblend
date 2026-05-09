<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
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

        $query->whereHas('activeVariants');

        $products = $query->orderBy('name')->take(50)->get();

        return response()->json($products->map(fn($p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'sku'      => $p->sku,
            'image_url'=> $p->image_url,
            'category' => $p->category->name,
            'category_id' => $p->category_id,
            'variants' => $p->activeVariants->map(fn($v) => [
                'id'             => $v->id,
                'size'           => $v->size,
                'color'          => $v->color,
                'price'          => $v->price,
                'stock_quantity' => $v->stock_quantity,
                'variant_info'   => $v->variant_info,
                'sku'            => $v->sku,
            ]),
        ]));
    }
}
