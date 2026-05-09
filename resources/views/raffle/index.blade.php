@extends('layouts.app')
@section('title', 'Raffle')
@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Raffle</h2>
        <button onclick="$('#start-modal').removeClass('hidden')"
            class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-plus mr-1"></i> New Raffle Period
        </button>
    </div>

    {{-- Active Period Banner --}}
    @if($activePeriod)
    <div class="bg-gray-900 text-white rounded-xl px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="bg-white bg-opacity-20 rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div>
                <p class="font-bold text-lg leading-tight">{{ $activePeriod->name }}</p>
                <p class="text-gray-300 text-xs mt-0.5">
                    Started {{ $activePeriod->starts_at->format('M d, Y') }}
                    @if($activePeriod->ends_at) &nbsp;·&nbsp; Ends {{ $activePeriod->ends_at->format('M d, Y') }} @endif
                    &nbsp;·&nbsp; {{ $activePeriod->total_entries }} {{ Str::plural('entry', $activePeriod->total_entries) }}
                </p>
            </div>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <button onclick="doDraw({{ $activePeriod->id }})"
                class="bg-white text-gray-800 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-semibold transition">
                <i class="fas fa-random mr-1"></i> Draw Winner
            </button>
            <button onclick="confirmEnd({{ $activePeriod->id }}, '{{ addslashes($activePeriod->name) }}')"
                class="bg-gray-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <i class="fas fa-flag-checkered mr-1"></i> End Raffle
            </button>
        </div>
    </div>
    @else
    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl p-8 text-center text-gray-400">
        <i class="fas fa-ticket-alt text-4xl mb-3 block"></i>
        <p class="font-semibold text-gray-500">No active raffle period</p>
        <p class="text-sm mt-1">Click "New Raffle Period" to start collecting entries.</p>
    </div>
    @endif

    {{-- Main content: sidebar + right --}}
    <div class="flex gap-5 items-start">

        {{-- Left: Period list --}}
        <div class="w-52 flex-shrink-0 bg-white rounded-xl shadow p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Periods</p>
            <div class="space-y-1">
                @forelse($periods as $p)
                <a href="{{ route('raffle.index', ['period_id' => $p->id]) }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition
                        {{ ($viewPeriod?->id == $p->id) ? 'bg-gray-900 text-white' : 'hover:bg-gray-50 text-gray-700' }}">
                    <span class="truncate font-medium">{{ $p->name }}</span>
                    <span class="ml-1 flex-shrink-0 text-xs px-1.5 py-0.5 rounded-full font-medium
                        {{ $p->status === 'active'
                            ? ($viewPeriod?->id == $p->id ? 'bg-white text-gray-800' : 'bg-gray-100 text-gray-700')
                            : ($viewPeriod?->id == $p->id ? 'bg-purple-400 text-white'  : 'bg-gray-100 text-gray-500') }}">
                        {{ $p->status === 'active' ? 'Active' : 'Ended' }}
                    </span>
                </a>
                @empty
                <p class="text-xs text-gray-400 px-2">No periods yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Right: stats + table --}}
        <div class="flex-1 min-w-0 space-y-4">

            @if($viewPeriod)

            {{-- Winner banner --}}
            @if($viewPeriod->status === 'ended' && $viewPeriod->winnerEntry)
            <div class="bg-gray-50 border border-gray-300 rounded-xl p-4 flex items-center gap-4">
                <div class="bg-yellow-400 rounded-full w-12 h-12 flex items-center justify-center text-white text-xl flex-shrink-0">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-600 font-semibold uppercase tracking-wider">🏆 Winner</p>
                    <p class="font-bold text-gray-800 text-lg">{{ $viewPeriod->winnerEntry->customer_name }}</p>
                    <p class="text-sm text-gray-500">
                        Ticket <span class="font-mono font-bold text-gray-700">{{ $viewPeriod->winner_ticket }}</span>
                        @if($viewPeriod->winnerEntry->customer_phone)
                            &nbsp;·&nbsp; {{ $viewPeriod->winnerEntry->customer_phone }}
                        @endif
                    </p>
                </div>
            </div>
            @endif

            {{-- Stats row --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
                    <div class="bg-gray-100 text-gray-700 rounded-full w-11 h-11 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Total Entries</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalEntries }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
                    <div class="bg-gray-100 text-gray-600 rounded-full w-11 h-11 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Winners Drawn</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $totalWinners }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-4 flex items-center gap-3">
                    <div class="bg-gray-100 text-gray-700 rounded-full w-11 h-11 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Spend per Entry</p>
                        <p class="text-2xl font-bold text-gray-700">₱{{ number_format($perEntry, 0) }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter bar --}}
            <form method="GET" class="bg-white rounded-xl shadow p-4 flex flex-wrap gap-3 items-center">
                <input type="hidden" name="period_id" value="{{ $viewPeriod->id }}">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name, phone or ticket..."
                    class="flex-1 min-w-48 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                    <input type="checkbox" name="winner" value="1" {{ request('winner') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-gray-700">
                    Winners only
                </label>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Filter</button>
                <a href="{{ route('raffle.index', ['period_id' => $viewPeriod->id]) }}"
                    class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm transition">Reset</a>
                @if($viewPeriod->isActive())
                <button type="button" onclick="doDraw({{ $viewPeriod->id }})"
                    class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition ml-auto">
                    <i class="fas fa-random mr-1"></i> Draw Winner
                </button>
                @endif
            </form>

            {{-- Entries table --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Customer</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Phone</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Transaction</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-600">Entries</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Ticket Numbers</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-600">Date</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($entries as $e)
                        <tr class="{{ $e->is_winner ? 'bg-gray-50' : 'hover:bg-gray-50' }}">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                @if($e->customer)
                                    <a href="{{ route('customers.show', $e->customer) }}" class="text-gray-900 hover:underline">{{ $e->customer_name }}</a>
                                @else
                                    {{ $e->customer_name }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $e->customer_phone ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-900">{{ $e->transaction->transaction_number }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full text-xs font-bold">{{ $e->entries }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 tracking-wider">{{ $e->ticket_numbers }}</td>
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $e->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($e->is_winner)
                                    <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-bold">
                                        <i class="fas fa-trophy mr-1"></i> Winner
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-400">
                                <i class="fas fa-ticket-alt text-3xl mb-2 block"></i>
                                No entries for this period yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4 border-t border-gray-100">{{ $entries->links() }}</div>
            </div>

            @else
            <div class="bg-white rounded-xl shadow p-12 text-center text-gray-400">
                <i class="fas fa-arrow-left text-2xl mb-2 block"></i>
                <p>Select a raffle period from the left to view entries.</p>
            </div>
            @endif

        </div>{{-- end right --}}
    </div>{{-- end flex --}}
</div>

{{-- Start New Period Modal --}}
<div id="start-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg text-gray-800"><i class="fas fa-ticket-alt text-gray-600 mr-2"></i>New Raffle Period</h3>
            <button onclick="$('#start-modal').addClass('hidden')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        @if($activePeriod)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4 text-sm text-gray-700">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Starting a new period will automatically end <strong>{{ $activePeriod->name }}</strong>.
        </div>
        @endif
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Raffle Name <span class="text-gray-600">*</span></label>
                <input type="text" id="p-name" placeholder="e.g. Summer Sale Raffle 2026"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="p-desc" rows="2" placeholder="Optional"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-gray-600">*</span></label>
                    <input type="date" id="p-start" value="{{ now()->toDateString() }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="date" id="p-end"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="startPeriod()" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2.5 rounded-lg text-sm font-medium transition">
                    <i class="fas fa-play mr-1"></i> Start Raffle
                </button>
                <button onclick="$('#start-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm transition">Cancel</button>
            </div>
        </div>
    </div>
</div>

{{-- End Raffle Confirm Modal --}}
<div id="end-modal" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-flag-checkered text-gray-600 text-2xl"></i>
        </div>
        <h3 class="font-bold text-lg text-gray-800 mb-1">End Raffle Period?</h3>
        <p class="text-gray-500 text-sm mb-1">You are about to end:</p>
        <p class="font-semibold text-gray-800 mb-3" id="end-period-name"></p>
        <p class="text-xs text-gray-400 mb-5">No new entries will be accepted. You can still draw a winner afterwards.</p>
        <div class="flex gap-3">
            <button onclick="doEndPeriod()" class="flex-1 bg-gray-500 hover:bg-red-600 text-white py-2.5 rounded-lg text-sm font-semibold transition">End Raffle</button>
            <button onclick="$('#end-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm transition">Cancel</button>
        </div>
    </div>
</div>

{{-- Draw Winner Modal --}}
<div id="draw-modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-sm mx-4 text-center">
        <div id="draw-spinning">
            <div class="text-6xl mb-4 animate-spin inline-block">🎰</div>
            <p class="text-gray-500 text-sm">Drawing winner...</p>
        </div>
        <div id="draw-result" class="hidden">
            <div class="text-6xl mb-3">🏆</div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1" id="draw-customer"></h3>
            <p class="text-gray-500 text-sm mb-2" id="draw-phone"></p>
            <div class="bg-gray-900 rounded-xl p-4 my-4">
                <p class="text-xs text-gray-300 mb-1">Winning Ticket</p>
                <p class="text-4xl font-bold font-mono text-white tracking-widest" id="draw-ticket"></p>
            </div>
            <p class="text-xs text-gray-400 mb-4" id="draw-txn"></p>
            <div class="flex gap-3">
                <button onclick="$('#draw-modal').addClass('hidden')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 rounded-lg text-sm transition">Close</button>
                <button onclick="location.reload()" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2.5 rounded-lg text-sm font-medium transition">View Entries</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let endPeriodId = null;

function startPeriod() {
    const name  = $('#p-name').val().trim();
    const start = $('#p-start').val();
    if (!name || !start) { showToast('Name and start date are required.', 'error'); return; }
    $.ajax({
        url: '{{ route("raffle.period.start") }}', method: 'POST',
        data: { name, description: $('#p-desc').val(), starts_at: start, ends_at: $('#p-end').val() },
        success: () => { showToast('Raffle period started!'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}

function confirmEnd(id, name) {
    endPeriodId = id;
    $('#end-period-name').text(name);
    $('#end-modal').removeClass('hidden');
}

function doEndPeriod() {
    $.ajax({
        url: `/raffle/period/${endPeriodId}/end`, method: 'POST',
        success: () => { showToast('Raffle ended.'); location.reload(); },
        error: xhr => showToast(xhr.responseJSON?.message || 'Error', 'error')
    });
}

function doDraw(periodId) {
    $('#draw-spinning').removeClass('hidden');
    $('#draw-result').addClass('hidden');
    $('#draw-modal').removeClass('hidden');
    setTimeout(() => {
        $.ajax({
            url: '{{ route("raffle.draw") }}', method: 'POST',
            data: { period_id: periodId },
            success: function(res) {
                $('#draw-spinning').addClass('hidden');
                $('#draw-customer').text(res.customer);
                $('#draw-phone').text(res.phone || '');
                $('#draw-ticket').text(res.ticket);
                $('#draw-txn').text('Transaction: ' + res.transaction);
                $('#draw-result').removeClass('hidden');
            },
            error: function(xhr) {
                $('#draw-modal').addClass('hidden');
                showToast(xhr.responseJSON?.message || 'No entries available.', 'error');
            }
        });
    }, 1800);
}
</script>
@endpush
