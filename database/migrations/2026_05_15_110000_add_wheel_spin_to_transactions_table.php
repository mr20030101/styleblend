<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('wheel_spin_token', 64)->nullable()->unique()->after('notes');
            $table->timestamp('wheel_spun_at')->nullable()->after('wheel_spin_token');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['wheel_spin_token', 'wheel_spun_at']);
        });
    }
};
