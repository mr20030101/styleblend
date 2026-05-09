<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raffle_entries', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('transaction_id')
                  ->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('raffle_entries', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Customer::class);
            $table->dropColumn('customer_id');
        });
    }
};
