@extends('layouts.app')
@section('title', 'Edit Product')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('products.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">Edit Product</h2>
    </div>

    @if($errors->any())
    <div class="bg-gray-50 border border-gray-300 text-gray-700 px-4 py-3 rounded-lg mb-4 text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Product Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @foreach($cat->children as $child)
                                <option value="{{ $child->id }}" {{ $product->category_id == $child->id ? 'selected' : '' }}>— {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                    @if($product->image)
                        <img src="{{ $product->image_url }}" class="w-16 h-16 rounded-lg object-cover mb-2">
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select name="gender" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="">Not specified</option>
                        @foreach(\App\Models\Product::GENDERS as $val => $label)
                            <option value="{{ $val }}" {{ old('gender', $product->gender) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_active" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="1" {{ $product->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$product->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg font-medium transition">Update Product</button>
            <a href="{{ route('products.barcodes', $product) }}" class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">
                <i class="fas fa-barcode mr-1"></i> Print Barcodes
            </a>
            <a href="{{ route('products.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">Cancel</a>
        </div>
    </form>

    <!-- Variants Management -->
    <div class="bg-white rounded-xl shadow p-6 mt-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h3 class="font-semibold text-gray-700">Variants</h3>
            <button onclick="showAddVariant()" class="bg-brand hover:bg-brand-dark text-white px-3 py-1.5 rounded-lg text-sm transition">
                <i class="fas fa-plus mr-1"></i> Add Variant
            </button>
        </div>

        <div id="variants-list" class="space-y-2">
            @foreach($product->variants as $v)
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg" id="vrow-{{ $v->id }}">
                <div class="flex-1 grid grid-cols-5 gap-3 text-sm">
                    <span class="font-medium">{{ $v->size }} / {{ $v->color }}</span>
                    <span class="text-gray-900 font-bold">₱{{ number_format($v->price, 2) }}</span>
                    <span class="text-gray-500 text-xs">Cost: ₱{{ number_format($v->cost_price, 2) }}</span>
                    <span class="{{ $v->stock_quantity <= 5 ? 'text-red-600 font-bold' : 'text-gray-600' }}">{{ $v->stock_quantity }} in stock</span>
                    @php $margin = $v->price > 0 ? (($v->price - $v->cost_price) / $v->price) * 100 : 0; @endphp
                    <span class="text-xs font-medium {{ $margin >= 30 ? 'text-gray-700' : ($margin > 0 ? 'text-yellow-600' : 'text-gray-400') }}">
                        {{ number_format($margin, 1) }}% margin
                    </span>
                </div>
                <button onclick="editVariant({{ $v->id }}, '{{ $v->size }}', '{{ $v->color }}', {{ $v->price }}, {{ $v->cost_price ?? 0 }}, {{ $v->stock_quantity }})"
                    class="text-gray-900 hover:text-indigo-800 text-sm px-2"><i class="fas fa-edit"></i></button>
                <button onclick="deleteVariant({{ $v->id }})" class="text-gray-600 hover:text-gray-700 text-sm px-2"><i class="fas fa-trash"></i></button>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Add/Edit Variant Modal -->
<div id="variant-modal" class="fixed inset-0 bg-black/50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="variant-modal-title">Add Variant</h3>
        <div class="space-y-3">
            <input type="hidden" id="edit-variant-id">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                    <input type="text" id="v-size" placeholder="e.g. M, L, 28, 6Y"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" id="v-color" placeholder="e.g. Red"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                    <input type="number" id="v-price" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                    <input type="number" id="v-cost" min="0" step="0.01" placeholder="0.00"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                    <input type="number" id="v-stock" min="0" placeholder="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveVariant()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="$('#variant-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
const productId = {{ $product->id }};

function showAddVariant() {
    $('#variant-modal-title').text('Add Variant');
    $('#edit-variant-id').val('');
    $('#v-size').val(''); $('#v-color').val(''); $('#v-price').val(''); $('#v-cost').val(''); $('#v-stock').val('');
    $('#variant-modal').removeClass('hidden');
}

function editVariant(id, size, color, price, cost, stock) {
    $('#variant-modal-title').text('Edit Variant');
    $('#edit-variant-id').val(id);
    $('#v-size').val(size); $('#v-color').val(color); $('#v-price').val(price); $('#v-cost').val(cost); $('#v-stock').val(stock);
    $('#variant-modal').removeClass('hidden');
}

function saveVariant() {
    const id = $('#edit-variant-id').val();
    const data = { size: $('#v-size').val(), color: $('#v-color').val(), price: $('#v-price').val(), stock_quantity: $('#v-stock').val(), cost_price: $('#v-cost').val() || 0 };
    const url = id ? `/variants/${id}` : `/products/${productId}/variants`;
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data,
        success: () => { showToast('Variant saved.'); location.reload(); },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}

function deleteVariant(id) {
    swalConfirm({ title: 'Delete Variant?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: `/variants/${id}`, method: 'DELETE',
            success: () => { showToast('Variant deleted.'); $(`#vrow-${id}`).remove(); },
            error: () => showToast('Error deleting variant.', 'error')
        });
    });
}
</script>
@endpush
