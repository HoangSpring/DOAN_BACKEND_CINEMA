<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('showtime_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showtime_id')->constrained('showtimes')->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained('seats')->cascadeOnDelete();
            $table->enum('status', ['available', 'holding', 'booked'])->default('available');
            $table->foreignId('held_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('hold_expires_at')->nullable();
            $table->timestamps();

            $table->unique(['showtime_id', 'seat_id']);
            $table->index(['status', 'hold_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtime_seats');
    }
};
