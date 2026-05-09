@extends('layouts.app')
@section('title', 'Transactions')
@section('content')
<div class="space-y-4">
    <h2 class="text-2xl font-bold text-gray-800">Transactions</h2>

    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search transaction #..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <input type="date" name="date" value="{{ request('date') }}"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <option value="">All Status</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
        </select>
        <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm transition hover:bg-gray-700">Filter</button>
        <a href="{{ route('transactions.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Transaction #</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Cashier</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Customer</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $t)
                <tr class="hover:bg-gray-50 {{ $t->status === 'voided' ? 'opacity-60' : '' }}">
                    <td class="px-4 py-3 font-mono text-xs font-medium text-gray-800">{{ $t->transaction_number }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->customer?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-semibold {{ $t->status === 'voided' ? 'line-through text-gray-400' : '' }}">
                        ₱{{ number_format($t->total, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $t->status === 'voided' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('transactions.show', $t) }}" class="text-gray-600 hover:text-gray-900 text-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="text-gray-600 hover:text-gray-900 text-sm">
                                <i class="fas fa-print"></i>
                            </a>
                            @if($t->status === 'completed')
                            <button onclick="openVoid({{ $t->id }}, '{{ $t->transaction_number }}')"
                                class="text-red-500 hover:text-red-700 text-sm">
                                <i class="fas fa-ban"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-12 text-gray-400">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $transactions->links() }}</div>
    </div>
</div>

<!-- Void Modal -->
<div id="void-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-ban text-red-500"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">Void Transaction</h3>
                <p class="text-xs text-gray-500" id="void-txn-num"></p>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-3">This will mark the transaction as voided and restore all stock. This cannot be undone.</p>
        <input type="hidden" id="void-txn-id">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
            <textarea id="void-reason" rows="2" placeholder="e.g. Customer returned items, wrong order..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
        </div>
        <div class="flex gap-3">
            <button onclick="submitVoid()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                Void Transaction
            </button>
            <button onclick="$('#void-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm transition">Cancel</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openVoid(id, num) {
    $('#void-txn-id').val(id);
    $('#void-txn-num').text(num);
    $('#void-reason').val('');
    $('#void-modal').removeClass('hidden');
    setTimeout(() => $('#void-reason').focus(), 100);
}
function submitVoid() {
    const id     = $('#void-txn-id').val();
    const reason = $('#void-reason').val().trim();
    if (!reason) { showToast('Please enter a reason.', 'error'); return; }
    $.ajax({
        url: `/transactions/${id}/void`, method: 'POST',
        data: { reason },
        success: (res) => { showToast(res.message); $('#void-modal').addClass('hidden'); location.reload(); },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
</script>
@endpush
