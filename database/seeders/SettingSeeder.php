<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Store Info
            'store_name'         => 'Clothing Store POS',
            'store_address'      => '123 Main Street, Manila, Philippines',
            'store_phone'        => '+63 912 345 6789',
            'store_email'        => 'store@example.com',
            'store_footer'       => 'Thank you for shopping with us!',
            'store_logo'         => '',

            // Tax
            'tax_enabled'        => '1',
            'tax_rate'           => '12',        // percentage
            'tax_label'          => 'VAT',
            'tax_inclusive'      => '0',         // 0 = exclusive, 1 = inclusive

            // POS
            'currency_symbol'    => '₱',
            'low_stock_threshold' => '5',
            'receipt_auto_print' => '0',

            // Discount
            'discount_enabled'   => '1',
            'max_discount'       => '100',

            // Raffle
            'raffle_enabled'     => '1',
            'raffle_per_entry'   => '300',   // spend ₱300 = 1 entry
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
