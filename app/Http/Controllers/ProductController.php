<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'variants']);
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }
        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string',
            'variants.*.color' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.stock_quantity' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        foreach ($request->variants as $i => $v) {
            $product->variants()->create([
                'size' => $v['size'],
                'color' => $v['color'],
                'price' => $v['price'],
                'stock_quantity' => $v['stock_quantity'],
                'sku' => $data['sku'] . '-' . $v['size'] . '-' . strtoupper(substr($v['color'], 0, 3)) . $i,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function barcodes(Product $product)
    {
        $product->load('variants', 'category');

        $variantsData = $product->variants->map(function ($v) use ($product) {
            $sku = trim($v->sku ?? '', '-');
            $barcode = $sku ?: trim($product->sku . '-' . $v->size, '-');
            return [
                'id'      => $v->id,
                'size'    => $v->size,
                'color'   => $v->color,
                'price'   => $v->price,
                'sku'     => $sku ?: $barcode,
                'barcode' => $barcode,
            ];
        })->values();

        $currencySymbol = \App\Models\Setting::get('currency_symbol', '₱');
        return view('products.barcodes', compact('product', 'variantsData', 'currencySymbol'));
    }

    public function barcodesBatch()
    {
        $products = Product::with(['variants', 'category'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'category' => $p->category->name,
                    'variants' => $p->variants->map(function ($v) use ($p) {
                        $sku = trim($v->sku ?? '', '-');
                        $barcode = $sku ?: trim($p->sku . '-' . $v->size, '-');
                        return [
                            'id'      => $v->id,
                            'size'    => $v->size,
                            'color'   => $v->color,
                            'price'   => $v->price,
                            'barcode' => $barcode,
                        ];
                    })->values(),
                ];
            });

        $currencySymbol = \App\Models\Setting::get('currency_symbol', '₱');
        return view('products.barcodes_batch', compact('products', 'currencySymbol'));
    }

    public function edit(Product $product)
    {
        $product->load('variants');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return response()->json(['success' => true]);
    }

    public function storeVariant(Request $request, Product $product)
    {
        $data = $request->validate([
            'size' => 'required|string',
            'color' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);
        $data['sku'] = trim($product->sku . '-' . $data['size'] . '-' . strtoupper(substr($data['color'], 0, 3)) . rand(10, 99), '-');
        $variant = $product->variants()->create($data);
        return response()->json(['success' => true, 'variant' => $variant]);
    }

    public function updateVariant(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'size'           => 'required|string',
            'color'          => 'required|string',
            'price'          => 'required|numeric|min:0',
            'cost_price'     => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active'      => 'boolean',
        ]);
        $variant->update($data);
        return response()->json(['success' => true]);
    }

    public function destroyVariant(ProductVariant $variant)
    {
        $variant->delete();
        return response()->json(['success' => true]);
    }
}
