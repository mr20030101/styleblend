@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-gray-100 text-gray-900 rounded-full p-3 text-2xl"><i class="fas fa-tshirt"></i></div>
            <div>
                <p class="text-sm text-gray-500">Total Products</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalProducts }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-gray-100 text-gray-700 rounded-full p-3 text-2xl"><i class="fas fa-boxes"></i></div>
            <div>
                <p class="text-sm text-gray-500">Total Stock</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totalStock) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-gray-100 text-gray-700 rounded-full p-3 text-2xl"><i class="fas fa-receipt"></i></div>
            <div>
                <p class="text-sm text-gray-500">Sales Today</p>
                <p class="text-2xl font-bold text-gray-800">{{ $salesToday }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-gray-100 text-gray-600 rounded-full p-3 text-2xl"><i class="fas fa-peso-sign"></i></div>
            <div>
                <p class="text-sm text-gray-500">Revenue Today</p>
                <p class="text-2xl font-bold text-gray-800">₱{{ number_format($revenueToday, 2) }}</p>
                @if($expensesToday > 0)
                <p class="text-xs text-gray-400 mt-0.5">Expenses: -₱{{ number_format($expensesToday, 2) }}</p>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4 {{ $netProfitToday >= 0 ? 'border-l-4 border-gray-800' : 'border-l-4 border-red-400' }}">
            <div class="bg-gray-100 text-gray-600 rounded-full p-3 text-2xl"><i class="fas fa-chart-line"></i></div>
            <div>
                <p class="text-sm text-gray-500">Net Profit Today</p>
                <p class="text-2xl font-bold {{ $netProfitToday >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                    ₱{{ number_format($netProfitToday, 2) }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 bg-gray-900 rounded flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation text-white" style="font-size:11px;"></i>
                    </div>
                    <span class="font-semibold text-gray-800">Low Stock</span>
                </div>
                <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="text-xs text-gray-400 hover:text-gray-700">View all →</a>
            </div>
            @if($lowStock->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">All stock levels are healthy.</div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($lowStock as $v)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $v->product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $v->size }} / {{ $v->color }}</p>
                        </div>
                        <span class="ml-3 flex-shrink-0 text-xs font-bold px-2.5 py-1 rounded-full {{ $v->stock_quantity == 0 ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700' }}">
                            {{ $v->stock_quantity == 0 ? 'Out' : $v->stock_quantity . ' left' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('inventory.index', ['low_stock' => 1]) }}"
                    class="block text-center text-xs text-gray-400 hover:text-gray-700 py-3 border-t border-gray-100 hover:bg-gray-50 transition">
                    See all low stock →
                </a>
            @endif
        </div>

        <!-- Best Selling -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gray-900 rounded flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-fire text-white" style="font-size:11px;"></i>
                    </div>
                    <span class="font-semibold text-gray-800">Best Selling</span>
                </div>
                <a href="{{ route('reports.sales') }}" class="text-xs text-gray-400 hover:text-gray-700">View report →</a>
            </div>
            @if($bestSelling->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No sales data yet.</div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($bestSelling as $i => $item)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400">₱{{ number_format($item->total_revenue, 2) }} revenue</p>
                        </div>
                        <span class="text-sm font-bold text-gray-900 flex-shrink-0">{{ $item->total_qty }} sold</span>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('reports.sales') }}"
                    class="block text-center text-xs text-gray-400 hover:text-gray-700 py-3 border-t border-gray-100 hover:bg-gray-50 transition">
                    See full report →
                </a>
            @endif
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gray-900 rounded flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-receipt text-white" style="font-size:11px;"></i>
                    </div>
                    <span class="font-semibold text-gray-800">Recent Transactions</span>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs text-gray-400 hover:text-gray-700">View all →</a>
            </div>
            @if($recentTransactions->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">No transactions yet.</div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($recentTransactions as $t)
                    <a href="{{ route('transactions.show', $t) }}" class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 font-mono">{{ $t->transaction_number }}</p>
                            <p class="text-xs text-gray-400">{{ $t->created_at->format('M d, H:i') }} · {{ $t->user->name }}</p>
                        </div>
                        <div class="ml-3 flex-shrink-0 text-right">
                            <p class="text-sm font-bold text-gray-900">₱{{ number_format($t->total, 2) }}</p>
                            @if($t->status === 'voided')
                            <span class="text-xs text-red-500">Voided</span>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
                <a href="{{ route('transactions.index') }}"
                    class="block text-center text-xs text-gray-400 hover:text-gray-700 py-3 border-t border-gray-100 hover:bg-gray-50 transition">
                    See all transactions →
                </a>
            @endif
        </div>

    </div>
</div>
@endsection
