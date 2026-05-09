@extends('layouts.app')
@section('title', 'Suppliers')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Suppliers</h2>
        <button onclick="openModal()" class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> Add Supplier
        </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Contact</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Phone</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Orders</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $s->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->contact_person ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->phone ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->email ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $s->purchase_orders_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editSupplier({{ $s->id }},'{{ addslashes($s->name) }}','{{ $s->contact_person }}','{{ $s->phone }}','{{ $s->email }}','{{ addslashes($s->address ?? '') }}')"
                                class="text-gray-600 hover:text-gray-900"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteSupplier({{ $s->id }})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-12 text-gray-400">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="sup-modal" class="fixed inset-0 bg-black/50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="modal-title">Add Supplier</h3>
        <input type="hidden" id="sup-id">
        <div class="space-y-3">
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" id="s-name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-medium text-gray-700 mb-1">Contact Person</label>
                    <input type="text" id="s-contact" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" id="s-phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            </div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="s-email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                <input type="text" id="s-address" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveSupplier()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="$('#sup-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add Supplier'); $('#sup-id').val('');
    $('#s-name,#s-contact,#s-phone,#s-email,#s-address').val('');
    $('#sup-modal').removeClass('hidden');
}
function editSupplier(id, name, contact, phone, email, address) {
    $('#modal-title').text('Edit Supplier'); $('#sup-id').val(id);
    $('#s-name').val(name); $('#s-contact').val(contact); $('#s-phone').val(phone);
    $('#s-email').val(email); $('#s-address').val(address);
    $('#sup-modal').removeClass('hidden');
}
function saveSupplier() {
    const id = $('#sup-id').val();
    $.ajax({ url: id ? `/suppliers/${id}` : '/suppliers', method: id ? 'PUT' : 'POST',
        data: { name: $('#s-name').val(), contact_person: $('#s-contact').val(), phone: $('#s-phone').val(), email: $('#s-email').val(), address: $('#s-address').val() },
        success: () => { showToast('Supplier saved.'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
function deleteSupplier(id) {
    swalConfirm({ title: 'Delete Supplier?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: `/suppliers/${id}`, method: 'DELETE',
            success: () => { showToast('Deleted.'); location.reload(); },
            error: () => showToast('Error', 'error')
        });
    });
}
</script>
@endpush
