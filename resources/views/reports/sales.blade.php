@extends('layouts.app')
@section('title', 'Sales Report')
@section('content')
<div class="space-y-4">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Sales Report</h2>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Period</label>
            <select name="period" id="period-select" onchange="toggleDateFields()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>
        <div id="date-field" class="{{ $period == 'daily' ? '' : 'hidden' }}">
            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
            <input type="date" name="date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        </div>
        <div id="range-fields" class="{{ in_array($period, ['custom']) ? '' : 'hidden' }} flex gap-2">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
        </div>
        <button type="submit" class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm transition">Generate</button>
        <a href="{{ route('reports.sales.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-file-csv mr-1"></i> CSV
        </a>
        <a href="{{ route('reports.sales.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-file-pdf mr-1"></i> PDF
        </a>
    </form>

    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500">Transactions</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total_transactions'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500">Items Sold</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total_items'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Discount</p>
            <p class="text-2xl font-bold text-gray-700">-₱{{ number_format($summary['total_discount'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-700">₱{{ number_format($summary['total_revenue'], 2) }}</p>
        </div>
    </div>

    <!-- P&L Summary -->
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-semibold text-gray-700 mb-4 border-b pb-2">Profit & Loss</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Revenue</p>
                <p class="text-xl font-bold text-gray-800">₱{{ number_format($summary['total_revenue'], 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Cost of Goods</p>
                <p class="text-xl font-bold text-gray-700">-₱{{ number_format($summary['cogs'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Gross: ₱{{ number_format($summary['gross_profit'], 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Expenses</p>
                <p class="text-xl font-bold text-gray-700">-₱{{ number_format($summary['total_expenses'], 2) }}</p>
            </div>
            <div class="border-l pl-4">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Net Profit</p>
                <p class="text-2xl font-bold {{ $summary['net_profit'] >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                    ₱{{ number_format($summary['net_profit'], 2) }}
                </p>
            </div>
        </div>
    </div>

    @if($expenses->count())
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 font-semibold text-gray-700 flex justify-between">
            <span>Expenses in Period</span>
            <span class="text-gray-500 font-normal text-sm">₱{{ number_format($summary['total_expenses'], 2) }}</span>
        </div>
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($expenses as $exp)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-800">{{ $exp->title }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $exp->category }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $exp->expense_date->format('M d, Y') }}</td>
                    <td class="px-4 py-2 text-right font-medium">₱{{ number_format($exp->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Transaction #</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Cashier</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Items</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Subtotal</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Discount</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Receipt</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">{{ $t->transaction_number }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->user->name }}</td>
                    <td class="px-4 py-3 text-center">{{ $t->items->sum('quantity') }}</td>
                    <td class="px-4 py-3 text-right">₱{{ number_format($t->subtotal, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">-₱{{ number_format($t->discount, 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-700">₱{{ number_format($t->total, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="text-gray-900 hover:text-indigo-800 text-xs">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-12 text-gray-400">No transactions found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
function toggleDateFields() {
    const period = $('#period-select').val();
    $('#date-field').toggleClass('hidden', period !== 'daily');
    $('#range-fields').toggleClass('hidden', period !== 'custom');
}
</script>
@endpush
