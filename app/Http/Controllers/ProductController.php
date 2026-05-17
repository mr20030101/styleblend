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
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        return view('products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $isSimple = $request->input('product_type') === 'simple';

        $rules = [
            'name'        => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'gender'      => 'nullable|in:men,women,kids,unisex',
            'sku'         => 'required|string|unique:products,sku',
            'barcode'     => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'product_type' => 'required|in:simple,variable',
        ];

        if ($isSimple) {
            $rules['simple_price']         = 'required|numeric|min:0';
            $rules['simple_cost_price']    = 'nullable|numeric|min:0';
            $rules['simple_stock_quantity'] = 'required|integer|min:0';
        } else {
            $rules['variants']                  = 'required|array|min:1';
            $rules['variants.*.size']           = 'required|string';
            $rules['variants.*.color']          = 'required|string';
            $rules['variants.*.price']          = 'required|numeric|min:0';
            $rules['variants.*.stock_quantity'] = 'required|integer|min:0';
        }

        $data = $request->validate($rules);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($isSimple) {
            $product->variants()->create([
                'size'           => '',
                'color'          => '',
                'price'          => $request->simple_price,
                'cost_price'     => $request->simple_cost_price ?? 0,
                'stock_quantity' => $request->simple_stock_quantity,
                'sku'            => $data['sku'],
                'is_active'      => true,
            ]);
        } else {
            foreach ($request->variants as $i => $v) {
                $product->variants()->create([
                    'size'           => $v['size'],
                    'color'          => $v['color'],
                    'price'          => $v['price'],
                    'cost_price'     => $v['cost_price'] ?? 0,
                    'stock_quantity' => $v['stock_quantity'],
                    'sku'            => $data['sku'] . '-' . $v['size'] . '-' . strtoupper(substr($v['color'], 0, 3)) . $i,
                    'is_active'      => true,
                ]);
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function barcodes(Product $product)
    {
        $product->load('variants', 'category');

        $variantsData = $product->variants->map(function ($v) use ($product) {
            $sku = trim($v->sku ?? '', '-') ?: trim($product->sku ?? '', '-');
            return [
                'id'      => $v->id,
                'size'    => $v->size,
                'color'   => $v->color,
                'price'   => $v->price,
                'sku'     => $sku,
                'barcode' => $sku,
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
                        $sku = trim($v->sku ?? '', '-') ?: trim($p->sku ?? '', '-');
                        return [
                            'id'      => $v->id,
                            'size'    => $v->size,
                            'color'   => $v->color,
                            'price'   => $v->price,
                            'sku'     => $sku,
                            'barcode' => $sku,
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
        $categories = Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('is_active', true)->orderBy('name')->get();

        if (request()->ajax()) {
            return response()->json([
                'id'           => $product->id,
                'name'         => $product->name,
                'sku'          => $product->sku,
                'barcode'      => $product->barcode,
                'description'  => $product->description,
                'is_active'    => $product->is_active,
                'product_type' => $product->product_type,
                'category_id'  => $product->category_id,
                'brand_id'     => $product->brand_id,
                'gender'       => $product->gender,
                'image_url'    => $product->image_url,
                'has_image'    => (bool) $product->image,
                'variants'    => $product->variants->map(fn($v) => [
                    'id'             => $v->id,
                    'size'           => $v->size,
                    'color'          => $v->color,
                    'price'          => (float) $v->price,
                    'cost_price'     => (float) ($v->cost_price ?? 0),
                    'stock_quantity' => $v->stock_quantity,
                    'sku'            => $v->sku,
                ]),
            ]);
        }

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $isSimple = $product->product_type === 'simple';

        $rules = [
            'name'        => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'nullable|exists:brands,id',
            'gender'      => 'nullable|in:men,women,kids,unisex',
            'sku'         => 'required|string|unique:products,sku,' . $product->id,
            'barcode'     => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ];

        if ($isSimple) {
            $rules['simple_price']          = 'required|numeric|min:0';
            $rules['simple_cost_price']     = 'nullable|numeric|min:0';
            $rules['simple_stock_quantity'] = 'required|integer|min:0';
        }

        $data = $request->validate($rules);

        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        // For simple products, sync the single variant
        if ($isSimple) {
            $variant = $product->variants()->first();
            $variantData = [
                'price'          => $request->simple_price,
                'cost_price'     => $request->simple_cost_price ?? 0,
                'stock_quantity' => $request->simple_stock_quantity,
                'sku'            => $data['sku'],
            ];
            if ($variant) {
                $variant->update($variantData);
            } else {
                $product->variants()->create(array_merge($variantData, ['size' => '', 'color' => '', 'is_active' => true]));
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer|exists:products,id'])['ids'];
        $products = Product::whereIn('id', $ids)->get();
        foreach ($products as $product) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $product->delete();
        }
        return response()->json(['success' => true, 'deleted' => count($ids)]);
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
