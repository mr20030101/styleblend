<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useTailwind();

        try {
            $tz = \App\Models\Setting::get('timezone', 'Asia/Manila');
            config(['app.timezone' => $tz]);
            date_default_timezone_set($tz);
        } catch (\Exception $e) {
            // Database unavailable (e.g. during migrations)
        }
    }
}
