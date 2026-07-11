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
        Schema::create('showtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')->constrained('movies')->restrictOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('price_standard', 10, 2);
            $table->decimal('price_vip', 10, 2);
            $table->enum('status', ['scheduled', 'ongoing', 'ended', 'cancelled'])->default('scheduled')->index();
            $table->timestamps();

            $table->index(['room_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtimes');
    }
};
