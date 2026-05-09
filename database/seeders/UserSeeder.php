<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@styleblend.com'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $admin->assignRole('admin');

        // Shean Louise Margallo - Admin
        $shean = User::firstOrCreate(
            ['email' => 'sheanlouisemargallo@gmail.com'],
            ['name' => 'Shean Louise Margallo', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $shean->assignRole('admin');

        // Jayannet - Admin
        $jayannet = User::firstOrCreate(
            ['email' => 'jayannet4@gmail.com'],
            ['name' => 'Jay Anne', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $jayannet->assignRole('admin');

        // Default cashier user
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@styleblend.com'],
            ['name' => 'Cashier', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $cashier->assignRole('cashier');
    }
}
