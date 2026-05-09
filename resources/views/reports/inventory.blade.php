@extends('layouts.app')
@section('title', 'Inventory Report')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Inventory Report</h2>
        <a href="{{ route('reports.inventory.csv') }}" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-file-csv mr-1"></i> Export CSV
        </a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap">
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} class="rounded border-gray-300 text-gray-600">
            Show Low Stock Only (≤5)
        </label>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Filter</button>
        <a href="{{ route('reports.inventory') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
        @if($lowStockCount > 0)
        <span class="ml-auto bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-sm font-medium">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ $lowStockCount }} low stock items
        </span>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">SKU</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Size</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Color</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Price</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Stock</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($variants as $v)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $v->product->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $v->product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $v->sku }}</td>
                    <td class="px-4 py-3">{{ $v->size }}</td>
                    <td class="px-4 py-3">{{ $v->color }}</td>
                    <td class="px-4 py-3 text-right">₱{{ number_format($v->price, 2) }}</td>
                    <td class="px-4 py-3 text-center font-bold {{ $v->stock_quantity == 0 ? 'text-gray-700' : ($v->stock_quantity <= 5 ? 'text-gray-600' : 'text-gray-700') }}">
                        {{ $v->stock_quantity }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs
                            {{ $v->stock_quantity == 0 ? 'bg-gray-100 text-gray-700' : ($v->stock_quantity <= 5 ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $v->stock_quantity == 0 ? 'Out of Stock' : ($v->stock_quantity <= 5 ? 'Low Stock' : 'In Stock') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">No inventory data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
