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
        Schema::table('booking_seats', function (Blueprint $table) {
            $table->dropForeign(['showtime_seat_id']);
            $table->dropUnique(['showtime_seat_id']);
            $table->unique(['booking_id', 'showtime_seat_id']);
            $table->foreign('showtime_seat_id')->references('id')->on('showtime_seats')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_seats', function (Blueprint $table) {
            $table->dropForeign(['showtime_seat_id']);
            $table->dropUnique(['booking_id', 'showtime_seat_id']);
            $table->unique('showtime_seat_id');
            $table->foreign('showtime_seat_id')->references('id')->on('showtime_seats')->restrictOnDelete();
        });
    }
};
