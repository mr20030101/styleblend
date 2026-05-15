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
        <table class="w-full min-w-160 text-sm">
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
                @forelse($categories as $parent)
                {{-- Parent row --}}
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-gray-800">
                        {{ $parent->name }}
                        @if($parent->children->count())
                            <span class="ml-1 text-xs text-gray-400 font-normal">({{ $parent->children->count() }} sub)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $parent->description ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">{{ $parent->products_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $parent->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $parent->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editCategory({{ $parent->id }}, '{{ addslashes($parent->name) }}', '{{ addslashes($parent->description) }}', null, {{ $parent->is_active ? 'true' : 'false' }})"
                                class="text-gray-900 hover:text-indigo-800"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteCategory({{ $parent->id }})"
                                class="text-gray-600 hover:text-gray-700"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                {{-- Child rows --}}
                @foreach($parent->children as $child)
                <tr class="hover:bg-gray-50 bg-gray-50/50">
                    <td class="px-4 py-2.5 text-gray-700">
                        <span class="inline-flex items-center gap-1.5 pl-4">
                            <span class="text-gray-300 text-xs">└</span>
                            {{ $child->name }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $child->description ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-center text-xs">{{ $child->products->count() }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $child->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editCategory({{ $child->id }}, '{{ addslashes($child->name) }}', '{{ addslashes($child->description) }}', {{ $child->parent_id }}, {{ $child->is_active ? 'true' : 'false' }})"
                                class="text-gray-900 hover:text-indigo-800 text-xs"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteCategory({{ $child->id }})"
                                class="text-gray-600 hover:text-gray-700 text-xs"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr><td colspan="5" class="text-center py-12 text-gray-400">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="cat-modal" class="fixed inset-0 bg-black/50 z-40 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-full p-4">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md my-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-lg" id="modal-title">Add Category</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="space-y-3">
            <input type="hidden" id="cat-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" id="cat-name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                <select id="cat-parent"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="">None (Top Level)</option>
                    @foreach($parentOptions as $opt)
                        <option value="{{ $opt->id }}" data-id="{{ $opt->id }}">{{ $opt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="cat-desc" rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
            </div>
            <div id="cat-status-row" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="cat-status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveCategory()" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="closeModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add Category');
    $('#cat-id').val('');
    $('#cat-name').val('');
    $('#cat-parent').val('');
    $('#cat-desc').val('');
    $('#cat-status-row').addClass('hidden');
    // Re-enable all parent options
    $('#cat-parent option').prop('disabled', false).show();
    $('#cat-modal').removeClass('hidden');
    setTimeout(() => $('#cat-name').focus(), 100);
}

function closeModal() { $('#cat-modal').addClass('hidden'); }

function editCategory(id, name, desc, parentId, isActive) {
    $('#modal-title').text('Edit Category');
    $('#cat-id').val(id);
    $('#cat-name').val(name);
    $('#cat-desc').val(desc === 'null' ? '' : desc);
    $('#cat-parent').val(parentId || '');
    $('#cat-status').val(isActive ? '1' : '0');
    $('#cat-status-row').removeClass('hidden');
    // Disable selecting self as parent
    $('#cat-parent option').prop('disabled', false);
    $(`#cat-parent option[data-id="${id}"]`).prop('disabled', true);
    $('#cat-modal').removeClass('hidden');
    setTimeout(() => $('#cat-name').focus(), 100);
}

function saveCategory() {
    const id = $('#cat-id').val();
    const url = id ? `/categories/${id}` : '/categories';
    const method = id ? 'PUT' : 'POST';
    const data = {
        name: $('#cat-name').val(),
        description: $('#cat-desc').val(),
        parent_id: $('#cat-parent').val() || null,
    };
    if (id) data.is_active = $('#cat-status').val();

    $.ajax({ url, method, data,
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
