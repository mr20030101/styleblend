@extends('layouts.app')
@section('title', 'PO ' . $purchaseOrder->po_number)
@section('content')
<div class="max-w-3xl space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('purchase-orders.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">{{ $purchaseOrder->po_number }}</h2>
        <span class="px-2 py-0.5 rounded-full text-xs font-medium
            {{ $purchaseOrder->status === 'received' ? 'bg-gray-200 text-gray-700' : ($purchaseOrder->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
            {{ ucfirst($purchaseOrder->status) }}
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-5 text-sm space-y-2">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Order Info</h3>
            <div class="flex justify-between"><span class="text-gray-500">Supplier</span><span class="font-medium">{{ $purchaseOrder->supplier->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Order Date</span><span>{{ $purchaseOrder->order_date->format('M d, Y') }}</span></div>
            @if($purchaseOrder->received_date)
            <div class="flex justify-between"><span class="text-gray-500">Received</span><span>{{ $purchaseOrder->received_date->format('M d, Y') }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-gray-500">Created by</span><span>{{ $purchaseOrder->user->name }}</span></div>
            @if($purchaseOrder->notes)
            <div class="pt-1 text-gray-500 text-xs">{{ $purchaseOrder->notes }}</div>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow p-5 text-sm space-y-2">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Summary</h3>
            <div class="flex justify-between"><span class="text-gray-500">Items</span><span>{{ $purchaseOrder->items->count() }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Total Units</span><span>{{ $purchaseOrder->items->sum('quantity') }}</span></div>
            <div class="flex justify-between font-bold border-t pt-2"><span>Total Cost</span><span>₱{{ number_format($purchaseOrder->total_cost, 2) }}</span></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 font-semibold text-gray-700">Items</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Variant</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Qty</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Cost Price</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($purchaseOrder->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->productVariant->product->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $item->productVariant->size }} / {{ $item->productVariant->color }}</td>
                    <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-right">₱{{ number_format($item->cost_price, 2) }}</td>
                    <td class="px-4 py-3 text-right font-medium">₱{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($purchaseOrder->status === 'pending')
    <div class="flex gap-3">
        <button onclick="receiveOrder({{ $purchaseOrder->id }})"
            class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-check mr-1"></i> Mark as Received
        </button>
        <button onclick="cancelOrder({{ $purchaseOrder->id }})"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">
            <i class="fas fa-times mr-1"></i> Cancel Order
        </button>
    </div>
    @endif
</div>
@endsection
@push('scripts')
<script>
function receiveOrder(id) {
    swalConfirm({ title: 'Receive Order?', text: 'Stock will be updated accordingly.', confirmText: 'Yes, receive it!', icon: 'question' }, () => {
        $.ajax({ url: `/purchase-orders/${id}/receive`, method: 'POST',
            success: res => { showToast(res.message); location.reload(); },
            error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
        });
    });
}
function cancelOrder(id) {
    swalConfirm({ title: 'Cancel Order?', text: 'This action cannot be undone.', confirmText: 'Yes, cancel it!' }, () => {
        $.ajax({ url: `/purchase-orders/${id}/cancel`, method: 'POST',
            success: () => { showToast('Cancelled.'); window.location = '{{ route("purchase-orders.index") }}'; },
            error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
        });
    });
}
</script>
@endpush
