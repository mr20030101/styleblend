@extends('layouts.app')
@section('title', 'New Purchase Order')
@section('content')
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">New Purchase Order</h2>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
        @csrf
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Order Details</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                    <select name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Date *</label>
                    <input type="date" name="order_date" value="{{ now()->toDateString() }}" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h3 class="font-semibold text-gray-700">Items</h3>
                <button type="button" onclick="addItem()" class="bg-gray-900 hover:bg-gray-700 text-white px-3 py-1.5 rounded-lg text-sm transition">
                    <i class="fas fa-plus mr-1"></i> Add Item
                </button>
            </div>
            <div id="items-container" class="space-y-3"></div>
            <div class="mt-4 text-right font-semibold text-gray-700">
                Total: ₱<span id="po-total">0.00</span>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-medium transition">Create Order</button>
            <a href="{{ route('purchase-orders.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2.5 rounded-lg font-medium transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
const products = @json($productsJson);
let itemCount = 0;

function addItem() {
    const idx = itemCount++;
    let variantOptions = '<option value="">Select variant</option>';
    products.forEach(p => {
        p.active_variants.forEach(v => {
            variantOptions += `<option value="${v.id}" data-cost="${v.cost_price}">${p.name} — ${v.size}/${v.color} (₱${parseFloat(v.price).toFixed(2)})</option>`;
        });
    });

    const html = `
    <div class="grid grid-cols-4 gap-3 items-end bg-gray-50 p-3 rounded-lg" id="item-${idx}">
        <div class="col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Product Variant *</label>
            <select name="items[${idx}][variant_id]" required onchange="fillCost(this, ${idx})"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                ${variantOptions}
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Qty *</label>
            <input type="number" name="items[${idx}][quantity]" required min="1" placeholder="0" oninput="calcTotal()"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cost Price *</label>
            <input type="number" name="items[${idx}][cost_price]" id="cost-${idx}" required min="0" step="0.01" placeholder="0.00" oninput="calcTotal()"
                class="w-full border border-gray-300 rounded px-2 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
        </div>
        <div class="col-span-4 flex justify-end">
            <button type="button" onclick="document.getElementById('item-${idx}').remove(); calcTotal();"
                class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash mr-1"></i>Remove</button>
        </div>
    </div>`;
    $('#items-container').append(html);
}

function fillCost(sel, idx) {
    const cost = sel.options[sel.selectedIndex]?.dataset?.cost || '';
    document.getElementById(`cost-${idx}`).value = cost;
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('#items-container .bg-gray-50').forEach(row => {
        const qty  = parseFloat(row.querySelector('input[name*="quantity"]')?.value) || 0;
        const cost = parseFloat(row.querySelector('input[name*="cost_price"]')?.value) || 0;
        total += qty * cost;
    });
    document.getElementById('po-total').textContent = total.toFixed(2);
}

addItem();
</script>
@endpush
