<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffCounterBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_counter_booking_route_is_accessible_with_session_auth()
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->withHeaders([
            'Idempotency-Key' => 'test-key',
        ])->postJson('/api/staff/bookings/counter', []);

        $response->assertStatus(422);
    }

    public function test_staff_can_cancel_counter_booking_and_release_seats()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $movie = Movie::create([
            'title' => 'Test Movie',
            'duration_minutes' => 120,
            'genre' => 'Action',
            'age_rating' => 'T13',
        ]);
        $room = Room::create(['name' => 'Room A', 'room_type' => '2D']);
        $seat = Seat::create([
            'room_id' => $room->id,
            'seat_row' => 'A',
            'seat_number' => 1,
            'seat_type' => 'standard',
        ]);
        $showtime = Showtime::create([
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->addHours(2),
            'price_standard' => 70000,
            'price_vip' => 90000,
            'status' => 'scheduled',
        ]);
        $showtimeSeat = ShowtimeSeat::create([
            'showtime_id' => $showtime->id,
            'seat_id' => $seat->id,
            'status' => 'booked',
        ]);
        $booking = Booking::create([
            'staff_id' => $staff->id,
            'showtime_id' => $showtime->id,
            'booking_code' => 'BKTEST1234',
            'total_amount' => 70000,
            'status' => 'paid',
            'booking_type' => 'counter',
        ]);
        BookingSeat::create([
            'booking_id' => $booking->id,
            'showtime_seat_id' => $showtimeSeat->id,
            'price' => 70000,
        ]);

        $response = $this->actingAs($staff)->postJson('/api/staff/bookings/' . $booking->id . '/cancel');

        $response->assertOk();
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame('available', $showtimeSeat->fresh()->status);
        $this->assertNull($showtimeSeat->fresh()->held_by_user_id);
    }
}
