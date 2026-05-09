@extends('layouts.app')
@section('title', $customer->name)
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('customers.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-2xl font-bold text-gray-800">{{ $customer->name }}</h2>
        <span class="px-2 py-0.5 rounded-full text-xs {{ $customer->is_active ? 'bg-gray-100 text-gray-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $customer->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Profile -->
        <div class="bg-white rounded-xl shadow p-5 space-y-3">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Profile</h3>
            <div class="space-y-2 text-sm">
                <div class="flex gap-2"><i class="fas fa-phone w-4 text-gray-400"></i><span>{{ $customer->phone ?? 'N/A' }}</span></div>
                <div class="flex gap-2"><i class="fas fa-envelope w-4 text-gray-400"></i><span>{{ $customer->email ?? 'N/A' }}</span></div>
                <div class="flex gap-2"><i class="fas fa-map-marker-alt w-4 text-gray-400"></i><span>{{ $customer->address ?? 'N/A' }}</span></div>
                <div class="flex gap-2"><i class="fas fa-birthday-cake w-4 text-gray-400"></i><span>{{ $customer->birthday?->format('M d, Y') ?? 'N/A' }}</span></div>
                <div class="flex gap-2"><i class="fas fa-calendar w-4 text-gray-400"></i><span>Member since {{ $customer->created_at->format('M Y') }}</span></div>
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-white rounded-xl shadow p-5 space-y-3">
            <h3 class="font-semibold text-gray-700 border-b pb-2">Purchase Summary</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm"><span class="text-gray-500">Total Transactions</span><span class="font-bold">{{ $customer->transactions->count() }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Total Spent</span><span class="font-bold text-gray-700">₱{{ number_format($customer->total_spent, 2) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500">Avg. per Visit</span>
                    <span class="font-bold">₱{{ $customer->transactions->count() ? number_format($customer->total_spent / $customer->transactions->count(), 2) : '0.00' }}</span>
                </div>
            </div>
        </div>

        <!-- Raffle -->
        <div class="bg-white rounded-xl shadow p-5 space-y-3">
            <h3 class="font-semibold text-gray-700 border-b pb-2 flex items-center gap-2">
                <i class="fas fa-ticket-alt text-gray-600"></i> Raffle Entries
            </h3>
            <div class="text-center py-2">
                <p class="text-4xl font-bold text-gray-700">{{ $customer->total_raffle_entries }}</p>
                <p class="text-xs text-gray-400 mt-1">total entries earned</p>
            </div>
            @if($customer->raffleEntries->where('is_winner', true)->count())
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 text-center text-xs text-gray-700 font-medium">
                <i class="fas fa-trophy mr-1"></i> {{ $customer->raffleEntries->where('is_winner', true)->count() }} winning entry/entries
            </div>
            @endif
            <div class="space-y-1 max-h-32 overflow-y-auto">
                @foreach($customer->raffleEntries as $re)
                <div class="text-xs text-gray-500 flex justify-between">
                    <span>{{ $re->entries }} {{ Str::plural('entry', $re->entries) }}</span>
                    <span class="font-mono text-gray-400">{{ $re->ticket_numbers }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-100 font-semibold text-gray-700">Transaction History</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Transaction #</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Items</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">Receipt</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customer->transactions->sortByDesc('created_at') as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-900">{{ $t->transaction_number }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $t->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">{{ $t->items->sum('quantity') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-700">₱{{ number_format($t->total, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('pos.receipt', $t->id) }}" target="_blank" class="text-gray-900 hover:text-indigo-800 text-xs"><i class="fas fa-print"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
