<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raffle_entries', function (Blueprint $table) {
            $table->foreignId('raffle_period_id')->nullable()->after('id')
                  ->constrained('raffle_periods')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('raffle_entries', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\RafflePeriod::class);
            $table->dropColumn('raffle_period_id');
        });
    }
};
