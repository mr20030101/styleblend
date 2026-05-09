<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RafflePeriod;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json([
            'store_name'      => Setting::get('store_name', 'StyleBlend'),
            'store_address'   => Setting::get('store_address', ''),
            'store_phone'     => Setting::get('store_phone', ''),
            'currency_symbol' => Setting::get('currency_symbol', '₱'),
            'tax_enabled'     => Setting::get('tax_enabled', '1') == '1',
            'tax_rate'        => (float) Setting::get('tax_rate', '12'),
            'tax_label'       => Setting::get('tax_label', 'VAT'),
            'tax_inclusive'   => Setting::get('tax_inclusive', '0') == '1',
            'discount_enabled'=> Setting::get('discount_enabled', '1') == '1',
            'max_discount'    => (float) Setting::get('max_discount', '0'),
            'raffle_enabled'  => Setting::get('raffle_enabled', '1') == '1',
            'raffle_per_entry'=> (float) Setting::get('raffle_per_entry', '300'),
            'raffle_active'   => RafflePeriod::getActive() !== null,
        ]);
    }
}
