@extends('layouts.app')
@section('title', 'Transaction ' . $transaction->transaction_number)
@section('content')
<div class="space-y-4 max-w-3xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('transactions.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $transaction->transaction_number }}</h2>
        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $transaction->status === 'voided' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
            {{ ucfirst($transaction->status) }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Transaction Info</h3>
            <div class="flex justify-between"><span class="text-gray-500">Date</span><span>{{ $transaction->created_at->format('M d, Y H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Cashier</span><span>{{ $transaction->user->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Customer</span><span>{{ $transaction->customer?->name ?? 'Walk-in' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Payment</span><span class="capitalize">{{ $transaction->payment_method }}</span></div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 space-y-2 text-sm">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Summary</h3>
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₱{{ number_format($transaction->subtotal, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>-₱{{ number_format($transaction->discount, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Tax</span><span>₱{{ number_format($transaction->tax, 2) }}</span></div>
            <div class="flex justify-between font-bold border-t pt-2"><span>Total</span><span>₱{{ number_format($transaction->total, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Paid</span><span>₱{{ number_format($transaction->amount_paid, 2) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Change</span><span>₱{{ number_format($transaction->change_amount, 2) }}</span></div>
        </div>
    </div>

    @if($transaction->status === 'voided')
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm">
        <p class="font-semibold text-red-700 mb-1"><i class="fas fa-ban mr-1"></i> Voided</p>
        <p class="text-gray-600">Reason: {{ $transaction->void_reason }}</p>
        <p class="text-gray-500 text-xs mt-1">By {{ $transaction->voidedBy?->name }} on {{ $transaction->voided_at?->format('M d, Y H:i') }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 font-semibold text-gray-700">Items</div>
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Variant</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Qty</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Unit Price</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($transaction->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product_name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $item->variant_info }}</td>
                    <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-3 text-right font-medium">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex gap-3">
        <a href="{{ route('pos.receipt', $transaction->id) }}" target="_blank"
            class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-print mr-1"></i> Print Receipt
        </a>
        @if($transaction->status === 'completed')
        <button onclick="openVoid({{ $transaction->id }}, '{{ $transaction->transaction_number }}')"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-ban mr-1"></i> Void Transaction
        </button>
        @endif
    </div>
</div>

<!-- Void Modal -->
<div id="void-modal" class="fixed inset-0 bg-black/50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-gray-800 mb-1">Void Transaction</h3>
        <p class="text-xs text-gray-500 mb-3" id="void-txn-num"></p>
        <input type="hidden" id="void-txn-id">
        <textarea id="void-reason" rows="2" placeholder="Reason for voiding..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
        <div class="flex gap-3">
            <button onclick="submitVoid()" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-semibold transition">Void</button>
            <button onclick="$('#void-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openVoid(id, num) {
    $('#void-txn-id').val(id); $('#void-txn-num').text(num); $('#void-reason').val('');
    $('#void-modal').removeClass('hidden');
}
function submitVoid() {
    const id = $('#void-txn-id').val(), reason = $('#void-reason').val().trim();
    if (!reason) { showToast('Reason required.', 'error'); return; }
    $.ajax({ url: `/transactions/${id}/void`, method: 'POST', data: { reason },
        success: () => { showToast('Transaction voided.'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
</script>
@endpush
