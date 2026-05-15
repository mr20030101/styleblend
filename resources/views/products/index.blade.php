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

    <!-- Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-160 text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Brand</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Gender</th>
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
                    <td class="px-4 py-3">
                        @if($product->gender)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ \App\Models\Product::GENDERS[$product->gender] }}</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
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
                            <button onclick="openEditModal({{ $product->id }})" class="text-gray-900 hover:text-gray-700 text-sm">
                                <i class="fas fa-edit"></i>
                            </button>
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
        </div>
        <div class="p-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
<!-- Edit Product Modal -->
<div id="edit-product-modal" class="fixed inset-0 bg-black/50 z-60 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-full p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl my-4">

        <!-- Header: always visible -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-800">Edit Product</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <!-- Loading spinner -->
        <div id="edit-modal-loading" class="py-16 text-center">
            <i class="fas fa-spinner fa-spin text-3xl text-gray-300"></i>
        </div>

        <!-- Content + footer (hidden until data loads) -->
        <div id="edit-modal-content" class="hidden">
            <div class="p-6 space-y-5">
                <div id="edit-modal-errors" class="hidden bg-gray-50 border border-gray-300 text-gray-700 px-4 py-3 rounded-lg text-sm"></div>

                <form id="edit-product-form" onsubmit="return false;">
                    @csrf
                    <div class="bg-gray-50 rounded-xl p-5 space-y-4">
                        <h4 class="font-semibold text-gray-700 border-b pb-2">Product Information</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                <input type="text" name="name" id="ep-name" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select name="category_id" id="ep-category" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @foreach($cat->children as $child)
                                            <option value="{{ $child->id }}">— {{ $child->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <select name="brand_id" id="ep-brand"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                    <option value="">No Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                <select name="gender" id="ep-gender"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                    <option value="">Not specified</option>
                                    @foreach(\App\Models\Product::GENDERS as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                                <input type="text" name="sku" id="ep-sku" required
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                                <input type="text" name="barcode" id="ep-barcode"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="is_active" id="ep-status"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                                <img id="ep-image-preview" src="" alt="" class="w-16 h-16 rounded-lg object-cover mb-2 hidden">
                                <input type="file" name="image" id="ep-image" accept="image/*"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="ep-description" rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Variants -->
                <div class="border rounded-xl p-5">
                    <div class="flex justify-between items-center border-b pb-2 mb-4">
                        <h4 class="font-semibold text-gray-700">Variants</h4>
                        <button onclick="showModalAddVariant()" class="bg-brand hover:bg-brand-dark text-white px-3 py-1.5 rounded-lg text-sm transition">
                            <i class="fas fa-plus mr-1"></i> Add Variant
                        </button>
                    </div>
                    <div id="modal-variants-list" class="space-y-2"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t px-6 py-4">
                <div class="flex gap-3">
                    <button id="ep-save-btn" onclick="saveEditProduct()"
                        class="bg-brand hover:bg-brand-dark text-white px-6 py-2 rounded-lg font-medium text-sm transition">Save Changes</button>
                    <a id="ep-barcode-link" href="#"
                        class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg font-medium text-sm transition">
                        <i class="fas fa-barcode mr-1"></i> Print Barcodes
                    </a>
                    <button onclick="closeEditModal()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium text-sm transition">Cancel</button>
                </div>
            </div>
        </div>

    </div>
    </div>
</div>

<!-- Variant sub-modal (sits above edit modal) -->
<div id="modal-variant-modal" class="fixed inset-0 bg-black/50 z-9999 hidden">
    <div class="flex items-center justify-center h-full">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="mvm-title">Add Variant</h3>
        <div class="space-y-3">
            <input type="hidden" id="mv-id">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                    <input type="text" id="mv-size" placeholder="e.g. M, L, 28"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" id="mv-color" placeholder="e.g. Red"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <input type="number" id="mv-price" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                    <input type="number" id="mv-cost" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <input type="number" id="mv-stock" min="0" placeholder="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveModalVariant()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="$('#modal-variant-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteProduct(id) {
    swalConfirm({ title: 'Delete Product?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: '/products/' + id, method: 'DELETE',
            success: () => { showToast('Product deleted.'); location.reload(); },
            error: () => showToast('Failed to delete.', 'error')
        });
    });
}

let editProductId = null;

function openEditModal(id) {
    editProductId = id;
    $('#edit-modal-loading').removeClass('hidden');
    $('#edit-modal-content').addClass('hidden');
    $('#edit-modal-errors').addClass('hidden').empty();
    $('#edit-product-modal').removeClass('hidden').scrollTop(0);

    $.ajax({
        url: `/products/${id}/edit`,
        success: function(data) {
            $('#ep-name').val(data.name);
            $('#ep-sku').val(data.sku);
            $('#ep-barcode').val(data.barcode || '');
            $('#ep-description').val(data.description || '');
            $('#ep-category').val(data.category_id);
            $('#ep-brand').val(data.brand_id || '');
            $('#ep-gender').val(data.gender || '');
            $('#ep-status').val(data.is_active ? '1' : '0');
            $('#ep-image').val('');
            if (data.has_image) {
                $('#ep-image-preview').attr('src', data.image_url).removeClass('hidden');
            } else {
                $('#ep-image-preview').addClass('hidden');
            }
            $('#ep-barcode-link').attr('href', `/products/${id}/barcodes`);
            renderModalVariants(data.variants);
            $('#edit-modal-loading').addClass('hidden');
            $('#edit-modal-content').removeClass('hidden');
        },
        error: function() {
            showToast('Failed to load product.', 'error');
            closeEditModal();
        }
    });
}

function closeEditModal() {
    $('#edit-product-modal').addClass('hidden');
    editProductId = null;
}

let modalVariantData = {};

function renderModalVariants(variants) {
    const list = $('#modal-variants-list');
    modalVariantData = {};
    if (!variants.length) {
        list.html('<p class="text-center text-gray-400 py-4 text-sm">No variants yet.</p>');
        return;
    }
    let html = '';
    variants.forEach(function(v) {
        modalVariantData[v.id] = v;
        const cost = parseFloat(v.cost_price) || 0;
        const margin = v.price > 0 ? (((v.price - cost) / v.price) * 100).toFixed(1) : 0;
        const stockClass = v.stock_quantity <= 5 ? 'text-red-600 font-bold' : 'text-gray-600';
        const label = [v.size, v.color].filter(Boolean).join(' / ') || '—';
        html += `<div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg" id="mvrow-${v.id}">
            <div class="flex-1 grid grid-cols-4 gap-3 text-sm">
                <span class="font-medium">${label}</span>
                <span class="font-bold text-gray-900">₱${parseFloat(v.price).toFixed(2)}</span>
                <span class="${stockClass}">${v.stock_quantity} in stock</span>
                <span class="text-xs text-gray-400">${margin}% margin</span>
            </div>
            <button onclick="showModalEditVariant(${v.id})"
                class="text-gray-900 hover:text-indigo-800 text-sm px-2"><i class="fas fa-edit"></i></button>
            <button onclick="deleteModalVariant(${v.id})"
                class="text-gray-600 hover:text-gray-700 text-sm px-2"><i class="fas fa-trash"></i></button>
        </div>`;
    });
    list.html(html);
}

function saveEditProduct() {
    const formData = new FormData(document.getElementById('edit-product-form'));
    formData.append('_method', 'PUT');
    $('#ep-save-btn').prop('disabled', true).text('Saving...');
    $('#edit-modal-errors').addClass('hidden').empty();

    $.ajax({
        url: `/products/${editProductId}`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            showToast('Product updated.');
            closeEditModal();
            location.reload();
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                let html = '<ul class="list-disc list-inside">';
                Object.values(errors).forEach(msgs => msgs.forEach(m => html += `<li>${m}</li>`));
                html += '</ul>';
                $('#edit-modal-errors').html(html).removeClass('hidden');
                $('#edit-product-modal').scrollTop(0);
            } else {
                showToast('Failed to update product.', 'error');
            }
        },
        complete: function() {
            $('#ep-save-btn').prop('disabled', false).text('Save Changes');
        }
    });
}

function showModalAddVariant() {
    $('#mvm-title').text('Add Variant');
    $('#mv-id').val('');
    $('#mv-size,#mv-color,#mv-price,#mv-cost,#mv-stock').val('');
    $('#modal-variant-modal').removeClass('hidden');
}

function showModalEditVariant(id) {
    const v = modalVariantData[id];
    if (!v) return;
    $('#mvm-title').text('Edit Variant');
    $('#mv-id').val(id);
    $('#mv-size').val(v.size || '');
    $('#mv-color').val(v.color || '');
    $('#mv-price').val(v.price);
    $('#mv-cost').val(v.cost_price || 0);
    $('#mv-stock').val(v.stock_quantity);
    $('#modal-variant-modal').removeClass('hidden');
}

function saveModalVariant() {
    const id = $('#mv-id').val();
    const data = {
        size: $('#mv-size').val(), color: $('#mv-color').val(),
        price: $('#mv-price').val(), stock_quantity: $('#mv-stock').val(),
        cost_price: $('#mv-cost').val() || 0
    };
    const url = id ? `/variants/${id}` : `/products/${editProductId}/variants`;
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data,
        success: function() {
            showToast('Variant saved.');
            $('#modal-variant-modal').addClass('hidden');
            $.ajax({ url: `/products/${editProductId}/edit`, success: (d) => renderModalVariants(d.variants) });
        },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error saving variant.', 'error')
    });
}

function deleteModalVariant(id) {
    swalConfirm({ title: 'Delete Variant?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: `/variants/${id}`, method: 'DELETE',
            success: () => { showToast('Variant deleted.'); $(`#mvrow-${id}`).remove(); },
            error: () => showToast('Error deleting variant.', 'error')
        });
    });
}
</script>
@endpush
