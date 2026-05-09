<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raffle_periods', function (Blueprint $table) {
            $table->foreign('winner_entry_id')->references('id')->on('raffle_entries')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('raffle_periods', function (Blueprint $table) {
            $table->dropForeign(['winner_entry_id']);
        });
    }
};