<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Booking;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CheckinDuplicateTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_duplicate_fails()
    {
        Schema::disableForeignKeyConstraints();
        
        $user = User::factory()->create(['role' => 'staff']);

        $showtime = Showtime::create([
            'movie_id' => 1,
            'room_id' => 1,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addMinutes(110),
            'price_standard' => 50000,
            'price_vip' => 70000,
            'status' => 'ongoing'
        ]);

        $booking = Booking::create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
            'booking_code' => 'BK12345',
            'total_amount' => 100000,
            'status' => 'paid',
            'booking_type' => 'online',
            'is_checked_in' => false
        ]);

        $payload = [
            'booking_id' => $booking->id,
            'code' => $booking->booking_code
        ];
        $signature = hash_hmac('sha256', json_encode($payload), env('QR_SECRET_KEY', 'secret'));

        $qrData = [
            'payload' => $payload,
            'sig' => $signature
        ];

        // First checkin
        $response1 = $this->actingAs($user)->postJson('/api/staff/checkin', [
            'qr_data' => $qrData
        ]);
        $response1->assertStatus(200);
        $this->assertTrue($booking->fresh()->is_checked_in);

        // Second checkin
        $response2 = $this->actingAs($user)->postJson('/api/staff/checkin', [
            'qr_data' => $qrData
        ]);
        $response2->assertStatus(422)
                 ->assertJsonFragment([
                     'error_code' => 'TICKET_ALREADY_CHECKED_IN'
                 ]);
                 
        Schema::enableForeignKeyConstraints();
    }
}
