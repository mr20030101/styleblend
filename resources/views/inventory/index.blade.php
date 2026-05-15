@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Inventory</h2>
        @if($lowStockCount > 0)
        <span class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-medium">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $lowStockCount }} low stock items
        </span>
        @endif
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
            class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @foreach($cat->children as $child)
                    <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>— {{ $child->name }}</option>
                @endforeach
            @endforeach
        </select>
        <select name="gender" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Genders</option>
            @foreach(\App\Models\Product::GENDERS as $val => $label)
                <option value="{{ $val }}" {{ request('gender') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-gray-300 text-gray-600">
            Low Stock Only
        </label>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Filter</button>
        <a href="{{ route('inventory.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-160 text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Variant</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">SKU</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Price</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Stock</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($variants as $v)
                <tr class="hover:bg-gray-50 {{ $v->stock_quantity == 0 ? 'bg-gray-50' : ($v->stock_quantity <= 5 ? 'bg-gray-50' : '') }}">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $v->product->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $v->product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $v->size }} / {{ $v->color }}</td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ trim($v->sku ?? '', '-') ?: trim($v->product->sku . ($v->size ? '-' . $v->size : ''), '-') }}</td>
                    <td class="px-4 py-3 text-right font-medium">₱{{ number_format($v->price, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $v->stock_quantity == 0 ? 'bg-gray-100 text-gray-700' : ($v->stock_quantity <= 5 ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $v->stock_quantity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="openAdjust({{ $v->id }}, '{{ $v->product->name }} ({{ $v->size }}/{{ $v->color }})', {{ $v->stock_quantity }})"
                                class="bg-gray-100 hover:bg-indigo-200 text-gray-800 px-2 py-1 rounded text-xs transition">
                                <i class="fas fa-edit mr-1"></i> Adjust
                            </button>
                            <button onclick="viewHistory({{ $v->id }})"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs transition">
                                <i class="fas fa-history mr-1"></i> History
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-12 text-gray-400">No inventory records found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $variants->links() }}</div>
    </div>
</div>

<!-- Adjust Modal -->
<div id="adjust-modal" class="fixed inset-0 bg-black/50 z-40 hidden">
    <div class="flex items-center justify-center h-full">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-1">Stock Adjustment</h3>
        <p class="text-sm text-gray-500 mb-4" id="adjust-product-name"></p>
        <div class="space-y-3">
            <input type="hidden" id="adjust-variant-id">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500">Current Stock</p>
                <p class="text-3xl font-bold text-gray-800" id="current-stock">0</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="adj-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="restock">Restock (Add)</option>
                    <option value="adjustment">Adjustment</option>
                    <option value="return">Return</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (use negative to deduct)</label>
                <input type="number" id="adj-qty" placeholder="e.g. 10 or -5"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" id="adj-notes" placeholder="Optional notes"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveAdjustment()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="$('#adjust-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- History Modal -->
<div id="history-modal" class="fixed inset-0 bg-black/50 z-40 hidden">
    <div class="flex items-center justify-center h-full">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Stock History</h3>
            <button onclick="$('#history-modal').addClass('hidden')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div id="history-content" class="max-h-96 overflow-y-auto space-y-2 text-sm"></div>
    </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openAdjust(id, name, stock) {
    $('#adjust-variant-id').val(id);
    $('#adjust-product-name').text(name);
    $('#current-stock').text(stock);
    $('#adj-qty').val(''); $('#adj-notes').val('');
    $('#adjust-modal').removeClass('hidden');
    setTimeout(() => $('#adj-qty').focus(), 100);
}

function saveAdjustment() {
    const id = $('#adjust-variant-id').val();
    $.ajax({
        url: `/inventory/${id}/adjust`, method: 'POST',
        data: { type: $('#adj-type').val(), quantity: $('#adj-qty').val(), notes: $('#adj-notes').val() },
        success: (res) => { showToast('Stock updated. New stock: ' + res.new_stock); $('#adjust-modal').addClass('hidden'); location.reload(); },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}

function viewHistory(id) {
    $('#history-content').html('<div class="text-center py-4 text-gray-400"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
    $('#history-modal').removeClass('hidden');
    $.get(`/inventory/${id}/history`, function(logs) {
        if (!logs.length) { $('#history-content').html('<p class="text-center text-gray-400 py-4">No history found.</p>'); return; }
        let html = '';
        logs.forEach(l => {
            const typeColors = { restock: 'bg-gray-100 text-gray-700', sale: 'bg-gray-100 text-gray-800', adjustment: 'bg-gray-100 text-gray-700', return: 'bg-gray-100 text-gray-800' };
            html += `
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium ${typeColors[l.type] || 'bg-gray-100 text-gray-600'}">${l.type}</span>
                <div class="flex-1">
                    <div class="flex justify-between">
                        <span class="font-medium ${l.quantity > 0 ? 'text-gray-700' : 'text-gray-700'}">${l.quantity > 0 ? '+' : ''}${l.quantity}</span>
                        <span class="text-gray-400 text-xs">${new Date(l.created_at).toLocaleString()}</span>
                    </div>
                    <div class="text-xs text-gray-400">${l.stock_before} → ${l.stock_after} ${l.notes ? '· ' + l.notes : ''}</div>
                </div>
            </div>`;
        });
        $('#history-content').html(html);
    });
}
</script>
@endpush
