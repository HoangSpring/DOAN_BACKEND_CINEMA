<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSnackSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_holding_seats_with_snack_items_updates_total_and_persists_items(): void
    {
        $user = User::factory()->create();
        $movie = Movie::create([
            'title' => 'Test Movie',
            'slug' => 'test-movie',
            'status' => 'showing',
            'poster_url' => '/images/poster.jpg',
            'duration_minutes' => 120,
            'genre' => 'Action',
            'age_rating' => 'P',
            'release_date' => now()->subDay(),
        ]);
        $room = Room::create([
            'name' => 'Test Room',
            'room_type' => '2D',
            'total_seats' => 1,
        ]);

        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDays(1),
            'end_time' => now()->addDays(1)->addMinutes(120),
            'price_standard' => 50000,
            'price_vip' => 70000,
            'status' => 'scheduled',
        ]);

        $seat = Seat::create([
            'room_id' => $room->id,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type' => 'standard',
        ]);

        $showtimeSeat = ShowtimeSeat::create([
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
            'status' => 'available',
        ]);

        $response = $this->actingAs($user)->postJson('/api/bookings/hold', [
            'showtime_id' => $showtime->id,
            'seat_ids' => [$showtimeSeat->seat_id],
            'items' => [
                ['id' => 'popcorn_small', 'quantity' => 2],
                ['id' => 'soda_large', 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201);

        $booking = Booking::latest('id')->first();

        $this->assertSame(155000.0, (float) $booking->total_amount);
        $this->assertSame([
            ['id' => 'popcorn_small', 'quantity' => 2, 'name' => 'Bắp rang nhỏ', 'price' => 35000],
            ['id' => 'soda_large', 'quantity' => 1, 'name' => 'Nước ngọt lớn', 'price' => 35000],
        ], $booking->items_data);
    }
}
