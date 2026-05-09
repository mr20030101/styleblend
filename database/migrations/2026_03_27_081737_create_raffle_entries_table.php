<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_entries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->integer('entries'); // number of entries earned from this transaction
            $table->string('ticket_numbers'); // comma-separated ticket numbers
            $table->boolean('is_winner')->default(false);
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_entries');
    }
};
