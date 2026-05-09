@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Categories</h2>
        <button onclick="openModal()" class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> Add Category
        </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Products</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $cat->description ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $cat->products_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $cat->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $cat->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editCategory({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->description }}')"
                                class="text-gray-900 hover:text-indigo-800"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteCategory({{ $cat->id }})"
                                class="text-gray-600 hover:text-gray-700"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-12 text-gray-400">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="cat-modal" class="fixed inset-0 bg-black/50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="modal-title">Add Category</h3>
        <div class="space-y-3">
            <input type="hidden" id="cat-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" id="cat-name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="cat-desc" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveCategory()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="closeModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add Category');
    $('#cat-id').val(''); $('#cat-name').val(''); $('#cat-desc').val('');
    $('#cat-modal').removeClass('hidden');
    setTimeout(() => $('#cat-name').focus(), 100);
}
function closeModal() { $('#cat-modal').addClass('hidden'); }
function editCategory(id, name, desc) {
    $('#modal-title').text('Edit Category');
    $('#cat-id').val(id); $('#cat-name').val(name); $('#cat-desc').val(desc);
    $('#cat-modal').removeClass('hidden');
}
function saveCategory() {
    const id = $('#cat-id').val();
    const url = id ? `/categories/${id}` : '/categories';
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: { name: $('#cat-name').val(), description: $('#cat-desc').val() },
        success: (res) => { showToast(res.message); closeModal(); location.reload(); },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
function deleteCategory(id) {
    swalConfirm({ title: 'Delete Category?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: `/categories/${id}`, method: 'DELETE',
            success: (res) => { showToast(res.message); location.reload(); },
            error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
        });
    });
}
</script>
@endpush
