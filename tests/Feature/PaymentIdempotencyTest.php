<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Schema;

class PaymentIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_callback_idempotency()
    {
        Schema::disableForeignKeyConstraints();
        
        $booking = Booking::create([
            'user_id' => 1,
            'showtime_id' => 1,
            'booking_code' => 'BKTEST123',
            'total_amount' => 100000,
            'status' => 'pending',
            'booking_type' => 'online',
        ]);

        $idempotencyKey = 'idempotent-key-12345';
        $payload = [
            'idempotency_key' => $idempotencyKey,
            'booking_id' => $booking->id,
            'amount' => 100000,
        ];

        // First call
        $response1 = $this->postJson('/api/payments/callback', $payload);

        $response1->assertStatus(200);

        // Second call
        $response2 = $this->postJson('/api/payments/callback', $payload);
        $response2->assertStatus(200)
            ->assertJsonFragment([
                'error_code' => 'PAYMENT_ALREADY_PROCESSED'
            ]);

        $this->assertEquals(1, Payment::where('idempotency_key', $idempotencyKey)->count());
        $this->assertEquals('paid', $booking->fresh()->status);
        
        Schema::enableForeignKeyConstraints();
    }
}
