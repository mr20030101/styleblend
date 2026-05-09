<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffle_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // e.g. "Summer Raffle 2026"
            $table->text('description')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();           // null = open-ended
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->string('winner_ticket')->nullable();   // drawn winning ticket
            $table->unsignedBigInteger('winner_entry_id')->nullable();   // will add foreign key later
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_periods');
    }
};
