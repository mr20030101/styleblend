@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Products</h2>
        <div class="flex gap-2">
            <a href="{{ route('products.import') }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-file-import mr-1"></i> Import CSV
            </a>
            <a href="{{ route('products.create') }}" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500">
        <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="brand_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-500">
            <option value="">All Brands</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Filter</button>
        <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Brand</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">SKU</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Variants</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Total Stock</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="w-10 h-10 rounded-lg object-cover bg-gray-100"
                                onerror="this.src='/images/no-image.png'">
                            <span class="font-medium text-gray-800">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->brand ? $product->brand->name : '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="px-4 py-3 text-center">{{ $product->variants->count() }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="{{ $product->total_stock <= 5 ? 'text-gray-700 font-bold' : 'text-gray-700' }}">
                            {{ $product->total_stock }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $product->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('products.barcodes', $product) }}" class="text-gray-500 hover:text-gray-800 text-sm" title="Print Barcodes">
                                <i class="fas fa-barcode"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="text-gray-900 hover:text-gray-700 text-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteProduct({{ $product->id }})" class="text-gray-600 hover:text-gray-700 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function deleteProduct(id) {
    if (!confirm('Delete this product?')) return;
    $.ajax({ url: '/products/' + id, method: 'DELETE',
        success: () => { showToast('Product deleted.'); location.reload(); },
        error: () => showToast('Failed to delete.', 'error')
    });
}
</script>
@endpush
