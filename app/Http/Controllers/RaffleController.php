<?php

namespace App\Http\Controllers;

use App\Models\RaffleEntry;
use App\Models\RafflePeriod;
use App\Models\Setting;
use Illuminate\Http\Request;

class RaffleController extends Controller
{
    public function index(Request $request)
    {
        $activePeriod = RafflePeriod::getActive();
        $periods      = RafflePeriod::orderBy('created_at', 'desc')->get();
        $perEntry     = (float) Setting::get('raffle_per_entry', 300);

        // Which period to view
        $viewPeriodId = $request->period_id ?? $activePeriod?->id;
        $viewPeriod   = $viewPeriodId ? RafflePeriod::find($viewPeriodId) : null;

        $query = RaffleEntry::with(['customer', 'transaction'])
            ->orderBy('created_at', 'desc');

        if ($viewPeriodId) {
            $query->where('raffle_period_id', $viewPeriodId);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_numbers', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->winner) {
            $query->where('is_winner', true);
        }

        $entries      = $query->paginate(25)->withQueryString();
        $totalEntries = $viewPeriod ? $viewPeriod->total_entries : RaffleEntry::sum('entries');
        $totalWinners = ($viewPeriodId
            ? RaffleEntry::where('raffle_period_id', $viewPeriodId)
            : RaffleEntry::query())->where('is_winner', true)->count();

        return view('raffle.index', compact(
            'entries', 'totalEntries', 'totalWinners', 'perEntry',
            'activePeriod', 'periods', 'viewPeriod'
        ));
    }

    public function startPeriod(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'starts_at'   => 'required|date',
            'ends_at'     => 'nullable|date|after_or_equal:starts_at',
        ]);

        // End any currently active period first
        RafflePeriod::where('status', 'active')->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        $period = RafflePeriod::create(array_merge($data, ['status' => 'active']));

        return response()->json(['success' => true, 'period' => $period]);
    }

    public function endPeriod(RafflePeriod $period)
    {
        if ($period->status === 'ended') {
            return response()->json(['success' => false, 'message' => 'Period already ended.'], 422);
        }

        $period->update(['status' => 'ended', 'ended_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Raffle period ended.']);
    }

    public function draw(Request $request)
    {
        $periodId = $request->period_id ?? RafflePeriod::getActive()?->id;

        $query = RaffleEntry::where('is_winner', false);
        if ($periodId) $query->where('raffle_period_id', $periodId);

        $winner = $query->inRandomOrder()->first();

        if (!$winner) {
            return response()->json(['success' => false, 'message' => 'No eligible entries to draw from.'], 422);
        }

        $tickets     = $winner->ticket_array;
        $drawnTicket = $tickets[array_rand($tickets)];

        $winner->update(['is_winner' => true]);

        // Save winner to period
        if ($periodId) {
            RafflePeriod::where('id', $periodId)->update([
                'winner_ticket'   => $drawnTicket,
                'winner_entry_id' => $winner->id,
            ]);
        }

        return response()->json([
            'success'     => true,
            'ticket'      => $drawnTicket,
            'customer'    => $winner->customer_name,
            'phone'       => $winner->customer_phone,
            'transaction' => $winner->transaction->transaction_number,
        ]);
    }

    public function markWinner(RaffleEntry $entry)
    {
        $entry->update(['is_winner' => !$entry->is_winner]);
        return response()->json(['success' => true, 'is_winner' => $entry->is_winner]);
    }
}
