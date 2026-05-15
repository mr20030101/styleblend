<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_prizes', function (Blueprint $table) {
            $table->enum('discount_type', ['fixed', 'percentage', 'none'])->default('none')->after('is_active');
            $table->decimal('discount_value', 8, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('wheel_prizes', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
