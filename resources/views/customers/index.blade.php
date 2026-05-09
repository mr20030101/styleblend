@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Customers</h2>
        <button onclick="openModal()" class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> Add Customer
        </button>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone or email..."
            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Search</button>
        <a href="{{ route('customers.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Phone</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Transactions</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Total Spent</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('customers.show', $c) }}" class="font-medium text-gray-900 hover:underline">{{ $c->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $c->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $c->email ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $c->transactions_count }}</td>
                    <td class="px-4 py-3 text-right font-medium">₱{{ number_format($c->transactions_sum_total ?? 0, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $c->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $c->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editCustomer({{ $c->id }}, '{{ addslashes($c->name) }}', '{{ $c->phone }}', '{{ $c->email }}', '{{ addslashes($c->address ?? '') }}', '{{ $c->birthday?->format('Y-m-d') }}', {{ $c->is_active ? 1 : 0 }})"
                                class="text-gray-900 hover:text-indigo-800"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteCustomer({{ $c->id }})" class="text-gray-600 hover:text-gray-700"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-12 text-gray-400">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $customers->links() }}</div>
    </div>
</div>

<!-- Modal -->
<div id="cust-modal" class="fixed inset-0 bg-black/50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="modal-title">Add Customer</h3>
        <div class="space-y-3">
            <input type="hidden" id="cust-id">
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Name *</label>
                    <input type="text" id="c-name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="c-phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="c-email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" id="c-address" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Birthday</label>
                    <input type="date" id="c-birthday" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div id="status-wrap" class="hidden">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select id="c-active" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="1">Active</option><option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveCustomer()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="$('#cust-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add Customer');
    $('#cust-id').val('');
    $('#c-name,#c-phone,#c-email,#c-address,#c-birthday').val('');
    $('#status-wrap').addClass('hidden');
    $('#cust-modal').removeClass('hidden');
    setTimeout(() => $('#c-name').focus(), 100);
}
function editCustomer(id, name, phone, email, address, birthday, active) {
    $('#modal-title').text('Edit Customer');
    $('#cust-id').val(id); $('#c-name').val(name); $('#c-phone').val(phone);
    $('#c-email').val(email); $('#c-address').val(address); $('#c-birthday').val(birthday);
    $('#c-active').val(active); $('#status-wrap').removeClass('hidden');
    $('#cust-modal').removeClass('hidden');
}
function saveCustomer() {
    const id = $('#cust-id').val();
    const url = id ? `/customers/${id}` : '/customers';
    const method = id ? 'PUT' : 'POST';
    const data = { name: $('#c-name').val(), phone: $('#c-phone').val(), email: $('#c-email').val(), address: $('#c-address').val(), birthday: $('#c-birthday').val() };
    if (id) data.is_active = $('#c-active').val();
    $.ajax({ url, method, data,
        success: () => { showToast('Customer saved.'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
function deleteCustomer(id) {
    swalConfirm({ title: 'Delete Customer?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: `/customers/${id}`, method: 'DELETE',
            success: () => { showToast('Deleted.'); location.reload(); },
            error: () => showToast('Error', 'error')
        });
    });
}
</script>
@endpush
