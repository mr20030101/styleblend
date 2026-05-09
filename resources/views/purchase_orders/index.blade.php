@extends('layouts.app')
@section('title', 'Purchase Orders')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Purchase Orders</h2>
        <a href="{{ route('purchase-orders.create') }}" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> New Order
        </a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">PO #</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Supplier</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Order Date</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Total Cost</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $o)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs font-medium text-gray-800">{{ $o->po_number }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $o->supplier->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $o->order_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right font-medium">₱{{ number_format($o->total_cost, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $o->status === 'received' ? 'bg-gray-200 text-gray-700' : ($o->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($o->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('purchase-orders.show', $o) }}" class="text-gray-600 hover:text-gray-900 text-sm"><i class="fas fa-eye"></i></a>
                            @if($o->status === 'pending')
                            <button onclick="receiveOrder({{ $o->id }})" class="text-gray-700 hover:text-gray-900 text-sm" title="Mark as Received">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            <button onclick="cancelOrder({{ $o->id }})" class="text-red-500 hover:text-red-700 text-sm" title="Cancel">
                                <i class="fas fa-times-circle"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-12 text-gray-400">No purchase orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function receiveOrder(id) {
    if (!confirm('Mark this order as received? Stock will be updated.')) return;
    $.ajax({ url: `/purchase-orders/${id}/receive`, method: 'POST',
        success: res => { showToast(res.message); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
function cancelOrder(id) {
    if (!confirm('Cancel this purchase order?')) return;
    $.ajax({ url: `/purchase-orders/${id}/cancel`, method: 'POST',
        success: () => { showToast('Order cancelled.'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
</script>
@endpush
