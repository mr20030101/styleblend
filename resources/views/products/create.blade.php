@extends('layouts.app')
@section('title', 'Add Product')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">Add Product</h2>
    </div>

    @if($errors->any())
    <div class="bg-gray-50 border border-gray-300 text-gray-700 px-4 py-3 rounded-lg mb-4 text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Product Type --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 border-b pb-2 mb-4">Product Type</h3>
            <div class="grid grid-cols-2 gap-4">
                <label id="type-simple-card"
                    class="relative flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition type-card border-brand bg-brand/5">
                    <input type="radio" name="product_type" value="simple" checked
                        class="mt-1 accent-brand" onchange="switchType('simple')">
                    <div>
                        <p class="font-semibold text-gray-800">Simple Product</p>
                        <p class="text-xs text-gray-500 mt-0.5">One price, one stock level. No size or color variants needed.</p>
                    </div>
                </label>
                <label id="type-variable-card"
                    class="relative flex items-start gap-4 p-4 rounded-xl border-2 cursor-pointer transition type-card border-gray-200">
                    <input type="radio" name="product_type" value="variable"
                        class="mt-1 accent-brand" onchange="switchType('variable')">
                    <div>
                        <p class="font-semibold text-gray-800">Variable Product</p>
                        <p class="text-xs text-gray-500 mt-0.5">Multiple variants with different sizes, colors, prices, or stock.</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Product Information --}}
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Product Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @foreach($cat->children as $child)
                                <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select name="brand_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Select brand (optional)</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Not specified</option>
                        @foreach(\App\Models\Product::GENDERS as $val => $label)
                            <option value="{{ $val }}" {{ old('gender') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    <input type="file" name="image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Simple product fields --}}
        <div id="simple-section" class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Pricing & Stock</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                    <input type="number" name="simple_price" value="{{ old('simple_price') }}" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                    <input type="number" name="simple_cost_price" value="{{ old('simple_cost_price') }}" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                    <input type="number" name="simple_stock_quantity" value="{{ old('simple_stock_quantity') }}" min="0" placeholder="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
        </div>

        {{-- Variable product variants --}}
        <div id="variable-section" class="bg-white rounded-xl shadow p-6 hidden">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h3 class="font-semibold text-gray-700">Variants</h3>
                <button type="button" onclick="addVariantRow()" class="bg-brand hover:bg-brand-dark text-white px-3 py-1.5 rounded-lg text-sm transition">
                    <i class="fas fa-plus mr-1"></i> Add Variant
                </button>
            </div>
            <div id="variants-container" class="space-y-3"></div>
            <p class="text-xs text-gray-400 mt-3">At least one variant is required.</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg font-medium transition">
                Save Product
            </button>
            <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
let variantCount = 0;

function switchType(type) {
    const isSimple = type === 'simple';
    document.getElementById('simple-section').classList.toggle('hidden', !isSimple);
    document.getElementById('variable-section').classList.toggle('hidden', isSimple);
    document.getElementById('type-simple-card').classList.toggle('border-brand', isSimple);
    document.getElementById('type-simple-card').classList.toggle('bg-brand/5', isSimple);
    document.getElementById('type-simple-card').classList.toggle('border-gray-200', !isSimple);
    document.getElementById('type-variable-card').classList.toggle('border-brand', !isSimple);
    document.getElementById('type-variable-card').classList.toggle('bg-brand/5', !isSimple);
    document.getElementById('type-variable-card').classList.toggle('border-gray-200', isSimple);
}

function addVariantRow() {
    const idx = variantCount++;
    const html = `
    <div class="variant-row grid grid-cols-6 gap-3 items-end bg-gray-50 p-3 rounded-lg" id="variant-${idx}">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Size *</label>
            <input type="text" name="variants[${idx}][size]" required placeholder="e.g. M"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand"
                list="size-suggestions">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Color *</label>
            <input type="text" name="variants[${idx}][color]" required placeholder="e.g. Red"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Price *</label>
            <input type="number" name="variants[${idx}][price]" required min="0" step="0.01" placeholder="0.00"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cost</label>
            <input type="number" name="variants[${idx}][cost_price]" min="0" step="0.01" placeholder="0.00"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Stock *</label>
            <input type="number" name="variants[${idx}][stock_quantity]" required min="0" placeholder="0"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
        </div>
        <div>
            <button type="button" onclick="document.getElementById('variant-${idx}').remove()"
                class="w-full bg-gray-100 hover:bg-red-200 text-gray-700 py-2 rounded text-sm transition">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>`;
    $('#variants-container').append(html);
}

// Size datalist suggestion
$('body').append('<datalist id="size-suggestions"><option value="XS"><option value="S"><option value="M"><option value="L"><option value="XL"><option value="XXL"><option value="Free Size"></datalist>');
</script>
@endpush
