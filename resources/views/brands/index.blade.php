@extends('layouts.app')
@section('title', 'Brands')
@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Brands</h2>
        <button onclick="openModal()" class="bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i>Add Brand
        </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Brand</th>
                    <th class="text-left px-6 py-3 font-semibold text-gray-600">Description</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Products</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-6 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($brands as $brand)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @if($brand->logo)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="w-10 h-10 rounded-lg object-cover">
                            @else
                                <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-500 font-semibold text-sm">{{ strtoupper(substr($brand->name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-gray-800">{{ $brand->name }}</div>
                                <div class="text-sm text-gray-500">{{ $brand->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ Str::limit($brand->description, 50) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-sm">
                            {{ $brand->products_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $brand->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $brand->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editBrand({{ $brand->id }})" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteBrand({{ $brand->id }})" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-tags text-4xl mb-3 text-gray-300"></i>
                        <p>No brands found. Create your first brand to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($brands->hasPages())
    <div class="flex justify-center">
        {{ $brands->links() }}
    </div>
    @endif
</div>

<!-- Brand Modal -->
<div id="brand-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-title" class="text-lg font-semibold text-gray-800">Add Brand</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="brand-form" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" id="brand-id" name="brand_id">
            <input type="hidden" id="form-method" name="_method" value="POST">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name *</label>
                <input type="text" id="brand-name" name="name" required 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="brand-description" name="description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                <input type="file" id="brand-logo" name="logo" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand">
                <div id="current-logo" class="mt-2 hidden">
                    <img id="logo-preview" src="" alt="Current logo" class="w-16 h-16 rounded-lg object-cover">
                </div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="brand-active" name="is_active" checked class="rounded border-gray-300">
                <label for="brand-active" class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" class="flex-1 bg-brand hover:bg-brand-dark text-white py-2 rounded-lg transition">
                    Save Brand
                </button>
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add Brand');
    $('#brand-form')[0].reset();
    $('#brand-id').val('');
    $('#form-method').val('POST');
    $('#current-logo').addClass('hidden');
    $('#brand-active').prop('checked', true);
    $('#brand-modal').removeClass('hidden').addClass('flex');
}

function closeModal() {
    $('#brand-modal').addClass('hidden').removeClass('flex');
}

function editBrand(id) {
    $.get(`/brands/${id}`, function(brand) {
        $('#modal-title').text('Edit Brand');
        $('#brand-id').val(brand.id);
        $('#form-method').val('PUT');
        $('#brand-name').val(brand.name);
        $('#brand-description').val(brand.description);
        $('#brand-active').prop('checked', brand.is_active);
        
        if (brand.logo) {
            $('#logo-preview').attr('src', brand.logo_url);
            $('#current-logo').removeClass('hidden');
        } else {
            $('#current-logo').addClass('hidden');
        }
        
        $('#brand-modal').removeClass('hidden').addClass('flex');
    });
}

function deleteBrand(id) {
    swalConfirm({ title: 'Delete Brand?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({
            url: `/brands/${id}`,
            method: 'DELETE',
            success: function(response) {
                showToast(response.message);
                location.reload();
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Error deleting brand', 'error');
            }
        });
    });
}

$('#brand-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const brandId = $('#brand-id').val();
    const method = $('#form-method').val();
    
    let url = '/brands';
    if (brandId) {
        url += `/${brandId}`;
        formData.append('_method', 'PUT');
    }
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            showToast(response.message);
            closeModal();
            location.reload();
        },
        error: function(xhr) {
            showToast(xhr.responseJSON?.message || 'Error saving brand', 'error');
        }
    });
});
</script>
@endpush