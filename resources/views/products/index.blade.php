@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Products</h2>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('products.barcodes.batch') }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-barcode mr-1"></i> Batch Barcodes
            </a>
            <a href="{{ route('products.export') }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-file-export mr-1"></i> Export CSV
            </a>
            <a href="{{ route('products.import') }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-file-import mr-1"></i> Import CSV
            </a>
            <a href="{{ route('products.create') }}" class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <i class="fas fa-plus mr-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @foreach($cat->children as $child)
                    <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>— {{ $child->name }}</option>
                @endforeach
            @endforeach
        </select>
        <select name="brand_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Brands</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
        </select>
        <select name="gender" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Genders</option>
            @foreach(\App\Models\Product::GENDERS as $val => $label)
                <option value="{{ $val }}" {{ request('gender') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Filter</button>
        <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
    </form>

    <!-- Bulk action bar -->
    <div id="bulk-bar" class="hidden bg-white rounded-xl shadow px-4 py-3 flex items-center gap-3">
        <span id="bulk-count" class="text-sm font-medium text-gray-700"></span>
        <button onclick="bulkDelete()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
            <i class="fas fa-trash mr-1"></i> Delete Selected
        </button>
        <button onclick="clearSelection()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-1.5 rounded-lg text-sm transition">
            Deselect All
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-160 text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 w-8">
                        <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"
                            class="rounded border-gray-300 text-brand focus:ring-brand">
                    </th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Brand</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Gender</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">SKU</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Type</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Total Stock</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50" id="row-{{ $product->id }}">
                    <td class="px-4 py-3">
                        <input type="checkbox" class="row-check rounded border-gray-300 text-brand focus:ring-brand"
                            value="{{ $product->id }}" onchange="updateBulkBar()">
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                class="w-10 h-10 rounded-lg object-cover bg-gray-100"
                                onerror="this.src='/images/no-image.svg'">
                            <span class="font-medium text-gray-800">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->brand ? $product->brand->name : '-' }}</td>
                    <td class="px-4 py-3">
                        @if($product->gender)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ \App\Models\Product::GENDERS[$product->gender] }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($product->product_type === 'simple')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">Simple</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">{{ $product->variants->count() }} variants</span>
                        @endif
                    </td>
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
                <tr><td colspan="10" class="text-center py-12 text-gray-400">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteProduct(id) {
    swalConfirm({ title: 'Delete Product?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: '/products/' + id, method: 'DELETE',
            success: () => { showToast('Product deleted.'); $('#row-' + id).remove(); updateBulkBar(); },
            error: () => showToast('Failed to delete.', 'error')
        });
    });
}

function getSelectedIds() {
    return $('.row-check:checked').map(function() { return parseInt(this.value); }).get();
}

function updateBulkBar() {
    const ids = getSelectedIds();
    const total = $('.row-check').length;
    if (ids.length > 0) {
        $('#bulk-count').text(ids.length + ' product' + (ids.length > 1 ? 's' : '') + ' selected');
        $('#bulk-bar').removeClass('hidden');
    } else {
        $('#bulk-bar').addClass('hidden');
    }
    $('#select-all').prop('indeterminate', ids.length > 0 && ids.length < total);
    $('#select-all').prop('checked', ids.length > 0 && ids.length === total);
}

function toggleSelectAll(el) {
    $('.row-check').prop('checked', el.checked);
    updateBulkBar();
}

function clearSelection() {
    $('.row-check, #select-all').prop('checked', false);
    $('#select-all').prop('indeterminate', false);
    $('#bulk-bar').addClass('hidden');
}

function bulkDelete() {
    const ids = getSelectedIds();
    if (!ids.length) return;
    swalConfirm({
        title: 'Delete ' + ids.length + ' product' + (ids.length > 1 ? 's' : '') + '?',
        text: 'This action cannot be undone.',
        confirmText: 'Yes, delete them!'
    }, () => {
        $.ajax({
            url: '{{ route("products.bulk-destroy") }}',
            method: 'DELETE',
            data: JSON.stringify({ ids, _token: '{{ csrf_token() }}' }),
            contentType: 'application/json',
            success: (res) => {
                showToast(res.deleted + ' product' + (res.deleted > 1 ? 's' : '') + ' deleted.');
                ids.forEach(id => $('#row-' + id).remove());
                clearSelection();
            },
            error: () => showToast('Failed to delete.', 'error')
        });
    });
}
</script>
@endpush
