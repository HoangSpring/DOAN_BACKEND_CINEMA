<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Support\Facades\Schema;

class SeatHoldRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_race_condition_when_holding_seat()
    {
        Schema::disableForeignKeyConstraints();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $showtime = Showtime::create([
            'movie_id' => 1,
            'room_id' => 1,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addMinutes(120),
            'price_standard' => 50000,
            'price_vip' => 70000,
            'status' => 'scheduled'
        ]);

        $seat = \App\Models\Seat::create([
            'room_id' => 1,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type' => 'standard'
        ]);

        $showtimeSeat = ShowtimeSeat::create([
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
            'status' => 'available'
        ]);

        $response1 = $this->actingAs($user1)->postJson('/api/bookings/hold', [
            'showtime_id' => $showtime->id,
            'seat_ids' => [$showtimeSeat->seat_id]
        ]);
        $response1->assertStatus(201);

        $response2 = $this->actingAs($user2)->postJson('/api/bookings/hold', [
            'showtime_id' => $showtime->id,
            'seat_ids' => [$showtimeSeat->seat_id]
        ]);
        $response2->assertStatus(409)
                 ->assertJsonFragment(['error_code' => 'SEAT_ALREADY_HELD']);

        Schema::enableForeignKeyConstraints();
    }
}
