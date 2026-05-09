@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Users</h2>
        <button onclick="openModal()" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> Add User
        </button>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Role</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->hasRole('admin') ? 'bg-gray-100 text-gray-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $user->getRoleNames()->first() ?? 'No Role' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $user->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->getRoleNames()->first() }}', {{ $user->is_active ? 1 : 0 }})"
                                class="text-gray-900 hover:text-indigo-800"><i class="fas fa-edit"></i></button>
                            @if($user->id !== auth()->id())
                            <button onclick="deleteUser({{ $user->id }})" class="text-gray-600 hover:text-gray-700"><i class="fas fa-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-12 text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="modal-title">Add User</h3>
        <div class="space-y-3">
            <input type="hidden" id="user-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                <input type="text" id="u-name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" id="u-email" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password <span id="pw-hint" class="text-gray-400 text-xs">(leave blank to keep)</span></label>
                <input type="password" id="u-password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                <select id="u-role" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div id="status-field" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="u-active" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveUser()" class="flex-1 bg-gray-900 hover:bg-gray-700 text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="closeModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add User');
    $('#user-id').val(''); $('#u-name').val(''); $('#u-email').val(''); $('#u-password').val('');
    $('#pw-hint').show(); $('#status-field').addClass('hidden');
    $('#user-modal').removeClass('hidden');
}
function closeModal() { $('#user-modal').addClass('hidden'); }
function editUser(id, name, email, role, active) {
    $('#modal-title').text('Edit User');
    $('#user-id').val(id); $('#u-name').val(name); $('#u-email').val(email);
    $('#u-role').val(role); $('#u-active').val(active);
    $('#pw-hint').show(); $('#status-field').removeClass('hidden');
    $('#user-modal').removeClass('hidden');
}
function saveUser() {
    const id = $('#user-id').val();
    const url = id ? `/users/${id}` : '/users';
    const method = id ? 'PUT' : 'POST';
    const data = { name: $('#u-name').val(), email: $('#u-email').val(), password: $('#u-password').val(), role: $('#u-role').val() };
    if (id) data.is_active = $('#u-active').val();
    $.ajax({ url, method, data,
        success: (res) => { showToast(res.message); closeModal(); location.reload(); },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
function deleteUser(id) {
    if (!confirm('Delete this user?')) return;
    $.ajax({ url: `/users/${id}`, method: 'DELETE',
        success: () => { showToast('User deleted.'); location.reload(); },
        error: (xhr) => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
</script>
@endpush
