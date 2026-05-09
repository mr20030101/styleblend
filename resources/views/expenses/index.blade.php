@extends('layouts.app')
@section('title', 'Expenses')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Expenses</h2>
        <button onclick="openModal()" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> Add Expense
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-gray-100 rounded-full w-11 h-11 flex items-center justify-center text-gray-600 text-lg flex-shrink-0">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">Today's Expenses</p>
                <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalToday, 2) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
            <div class="bg-gray-100 rounded-full w-11 h-11 flex items-center justify-center text-gray-600 text-lg flex-shrink-0">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400">This Month</p>
                <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalMonth, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-xl shadow p-4 flex gap-3 flex-wrap">
        <input type="date" name="date" value="{{ request('date') }}"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
        <input type="month" name="month" value="{{ request('month') }}"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
        <select name="category" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm transition hover:bg-gray-700">Filter</button>
        <a href="{{ route('expenses.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Title</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Notes</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Amount</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($expenses as $e)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $e->title }}</td>
                    <td class="px-4 py-3"><span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">{{ $e->category }}</span></td>
                    <td class="px-4 py-3 text-gray-600">{{ $e->expense_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $e->notes ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800">₱{{ number_format($e->amount, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="editExpense({{ $e->id }},'{{ addslashes($e->title) }}','{{ $e->category }}',{{ $e->amount }},'{{ $e->expense_date->format('Y-m-d') }}','{{ addslashes($e->notes ?? '') }}')"
                                class="text-gray-600 hover:text-gray-900"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteExpense({{ $e->id }})" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-12 text-gray-400">No expenses recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $expenses->links() }}</div>
    </div>
</div>

<!-- Modal -->
<div id="exp-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="font-bold text-lg mb-4" id="modal-title">Add Expense</h3>
        <input type="hidden" id="exp-id">
        <div class="space-y-3">
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" id="e-title" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="block text-xs font-medium text-gray-700 mb-1">Category *</label>
                    <select id="e-category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                        @foreach($categories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                    </select></div>
                <div><label class="block text-xs font-medium text-gray-700 mb-1">Amount *</label>
                    <input type="number" id="e-amount" min="0.01" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></div>
            </div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" id="e-date" value="{{ now()->toDateString() }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></div>
            <div><label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" id="e-notes" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></div>
            <div class="flex gap-3 pt-2">
                <button onclick="saveExpense()" class="flex-1 bg-gray-900 hover:bg-gray-700 text-white py-2 rounded-lg text-sm font-medium transition">Save</button>
                <button onclick="$('#exp-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function openModal() {
    $('#modal-title').text('Add Expense'); $('#exp-id').val('');
    $('#e-title,#e-amount,#e-notes').val(''); $('#e-date').val('{{ now()->toDateString() }}');
    $('#exp-modal').removeClass('hidden');
}
function editExpense(id, title, cat, amount, date, notes) {
    $('#modal-title').text('Edit Expense'); $('#exp-id').val(id);
    $('#e-title').val(title); $('#e-category').val(cat); $('#e-amount').val(amount);
    $('#e-date').val(date); $('#e-notes').val(notes);
    $('#exp-modal').removeClass('hidden');
}
function saveExpense() {
    const id = $('#exp-id').val();
    $.ajax({ url: id ? `/expenses/${id}` : '/expenses', method: id ? 'PUT' : 'POST',
        data: { title: $('#e-title').val(), category: $('#e-category').val(), amount: $('#e-amount').val(), expense_date: $('#e-date').val(), notes: $('#e-notes').val() },
        success: () => { showToast('Expense saved.'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}
function deleteExpense(id) {
    swalConfirm({ title: 'Delete Expense?', text: 'This action cannot be undone.', confirmText: 'Yes, delete it!' }, () => {
        $.ajax({ url: `/expenses/${id}`, method: 'DELETE',
            success: () => { showToast('Deleted.'); location.reload(); },
            error: () => showToast('Error', 'error')
        });
    });
}
</script>
@endpush
