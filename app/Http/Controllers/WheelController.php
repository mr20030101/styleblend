<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\WheelPrize;
use Illuminate\Http\Request;

class WheelController extends Controller
{
    public function index()
    {
        $prizes      = WheelPrize::orderBy('percentage', 'desc')->get();
        $total       = $prizes->sum('percentage');
        $minPurchase = (float) Setting::get('wheel_min_purchase', 0);
        $spinMode    = Setting::get('wheel_spin_mode', 'immediate');
        return view('wheel.index', compact('prizes', 'total', 'minPurchase', 'spinMode'));
    }

    public function activePrizes()
    {
        return response()->json(
            WheelPrize::where('is_active', true)->orderBy('id')->get(['id', 'name', 'color'])
        );
    }

    public function play(Request $request)
    {
        $activePrizes = WheelPrize::where('is_active', true)->orderBy('id')->get();
        $token        = $request->query('token');
        $minPurchase  = (float) Setting::get('wheel_min_purchase', 0);

        // Validate token if min purchase is set
        $tokenValid = false;
        $tokenError = null;

        if ($minPurchase > 0) {
            if (!$token) {
                $tokenError = 'A minimum purchase of ₱' . number_format($minPurchase, 2) . ' is required to spin.';
            } else {
                $txn = Transaction::where('wheel_spin_token', $token)->first();
                if (!$txn) {
                    $tokenError = 'Invalid or expired spin token.';
                } elseif ($txn->wheel_spun_at) {
                    $tokenError = 'This spin has already been used.';
                } else {
                    $tokenValid = true;
                }
            }
        } else {
            // No minimum set — free spin
            $tokenValid = true;
        }

        return view('wheel.play', compact('activePrizes', 'token', 'tokenValid', 'tokenError', 'minPurchase'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'min_purchase' => 'sometimes|numeric|min:0',
            'spin_mode'    => 'sometimes|in:immediate,next_purchase',
        ]);
        if ($request->has('min_purchase')) {
            Setting::set('wheel_min_purchase', $request->min_purchase);
        }
        if ($request->has('spin_mode')) {
            Setting::set('wheel_spin_mode', $request->spin_mode);
        }
        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:255',
            'color'          => 'required|string|size:7',
            'percentage'     => 'required|numeric|min:0.01|max:100',
            'is_active'      => 'boolean',
            'discount_type'  => 'nullable|in:fixed,percentage,none',
            'discount_value' => 'nullable|numeric|min:0',
        ]);
        $data['is_active']      = $request->boolean('is_active', true);
        $data['discount_type']  = $data['discount_type'] ?? 'none';
        $data['discount_value'] = $data['discount_value'] ?? 0;
        WheelPrize::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, WheelPrize $prize)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:255',
            'color'          => 'required|string|size:7',
            'percentage'     => 'required|numeric|min:0.01|max:100',
            'is_active'      => 'boolean',
            'discount_type'  => 'nullable|in:fixed,percentage,none',
            'discount_value' => 'nullable|numeric|min:0',
        ]);
        $data['is_active']      = $request->boolean('is_active', true);
        $data['discount_type']  = $data['discount_type'] ?? 'none';
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $prize->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(WheelPrize $prize)
    {
        $prize->delete();
        return response()->json(['success' => true]);
    }

    public function spin(Request $request)
    {
        $prizes      = WheelPrize::where('is_active', true)->get();
        $minPurchase = (float) Setting::get('wheel_min_purchase', 0);

        if ($prizes->isEmpty()) {
            return response()->json(['error' => 'No active prizes configured.'], 422);
        }

        // Validate token when min purchase is required
        if ($minPurchase > 0) {
            $token = $request->input('token');
            if (!$token) {
                return response()->json(['error' => 'Spin token required.'], 403);
            }

            $txn = Transaction::where('wheel_spin_token', $token)->first();
            if (!$txn) {
                return response()->json(['error' => 'Invalid or expired spin token.'], 403);
            }
            if ($txn->wheel_spun_at) {
                return response()->json(['error' => 'This spin has already been used.'], 403);
            }

            // Consume the token
            $txn->update(['wheel_spun_at' => now()]);
        }

        // Weighted random pick
        $total      = $prizes->sum('percentage');
        $rand       = mt_rand(0, (int)($total * 100)) / 100;
        $cumulative = 0;
        $winner     = $prizes->last();

        foreach ($prizes as $prize) {
            $cumulative += $prize->percentage;
            if ($rand <= $cumulative) {
                $winner = $prize;
                break;
            }
        }

        return response()->json([
            'prize' => [
                'id'             => $winner->id,
                'name'           => $winner->name,
                'description'    => $winner->description,
                'color'          => $winner->color,
                'discount_type'  => $winner->discount_type,
                'discount_value' => $winner->discount_value,
            ],
        ]);
    }

    public function spinFromPos(Request $request)
    {
        $request->validate(['cart_total' => 'required|numeric|min:0']);

        $prizes      = WheelPrize::where('is_active', true)->get();
        $minPurchase = (float) Setting::get('wheel_min_purchase', 0);

        if ($prizes->isEmpty()) {
            return response()->json(['error' => 'No active prizes configured.'], 422);
        }

        if ($minPurchase > 0 && $request->cart_total < $minPurchase) {
            return response()->json(['error' => 'Cart total does not meet minimum purchase requirement.'], 403);
        }

        $total      = $prizes->sum('percentage');
        $rand       = mt_rand(0, (int)($total * 100)) / 100;
        $cumulative = 0;
        $winner     = $prizes->last();

        foreach ($prizes as $prize) {
            $cumulative += $prize->percentage;
            if ($rand <= $cumulative) {
                $winner = $prize;
                break;
            }
        }

        return response()->json([
            'prize' => [
                'id'             => $winner->id,
                'name'           => $winner->name,
                'description'    => $winner->description,
                'color'          => $winner->color,
                'discount_type'  => $winner->discount_type,
                'discount_value' => $winner->discount_value,
            ],
        ]);
    }
}
