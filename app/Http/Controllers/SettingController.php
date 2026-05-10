<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        $tokens   = Auth::user()->tokens()->orderByDesc('created_at')->get();
        return view('settings.index', compact('settings', 'tokens'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name'          => 'required|string|max:100',
            'store_address'       => 'nullable|string|max:255',
            'store_phone'         => 'nullable|string|max:50',
            'store_email'         => 'nullable|email|max:100',
            'store_footer'        => 'nullable|string|max:255',
            'store_logo'          => 'nullable|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'tax_enabled'         => 'boolean',
            'tax_rate'            => 'required|numeric|min:0|max:100',
            'tax_label'           => 'required|string|max:20',
            'tax_inclusive'       => 'boolean',
            'currency_symbol'     => 'required|string|max:5',
            'low_stock_threshold' => 'required|integer|min:1',
            'receipt_auto_print'  => 'boolean',
            'discount_enabled'    => 'boolean',
            'max_discount'        => 'required|numeric|min:0',
            'raffle_enabled'      => 'boolean',
            'raffle_per_entry'    => 'required|numeric|min:1',
        ]);

        // Handle logo upload
        if ($request->hasFile('store_logo')) {
            $old = Setting::get('store_logo');
            if ($old) Storage::disk('public')->delete($old);
            $data['store_logo'] = $request->file('store_logo')->store('logos', 'public');
        } else {
            unset($data['store_logo']); // don't overwrite with null
        }

        // Handle logo removal
        if ($request->boolean('remove_logo')) {
            $old = Setting::get('store_logo');
            if ($old) Storage::disk('public')->delete($old);
            $data['store_logo'] = '';
        }

        // Checkboxes default to 0 when unchecked
        $data['tax_enabled']        = $request->boolean('tax_enabled') ? '1' : '0';
        $data['tax_inclusive']      = $request->boolean('tax_inclusive') ? '1' : '0';
        $data['receipt_auto_print'] = $request->boolean('receipt_auto_print') ? '1' : '0';
        $data['discount_enabled']   = $request->boolean('discount_enabled') ? '1' : '0';
        $data['raffle_enabled']     = $request->boolean('raffle_enabled') ? '1' : '0';

        Setting::setMany($data);

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully.');
    }

    public function createToken(Request $request)
    {
        $request->validate(['token_name' => 'required|string|max:100']);

        $token = Auth::user()->createToken($request->token_name)->plainTextToken;

        return redirect()->route('settings.index')
            ->with('new_token', $token)
            ->with('success', 'API token created. Copy it now — it will not be shown again.');
    }

    public function revokeToken(PersonalAccessToken $token)
    {
        if ($token->tokenable_id !== Auth::id()) {
            abort(403);
        }

        $token->delete();

        return redirect()->route('settings.index')->with('success', 'Token revoked.');
    }
}
