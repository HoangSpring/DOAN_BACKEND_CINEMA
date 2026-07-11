<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;

class BookingAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_checkout_other_customers_booking()
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $this->seed();

        $customerA = User::where('email', 'customer@example.com')->first();
        if (!$customerA) {
            $customerA = User::factory()->create(['role' => 'customer']);
        }
        $customerB = User::factory()->create(['role' => 'customer']);

        $bookingA = Booking::create([
            'user_id' => $customerA->id,
            'showtime_id' => 1,
            'booking_code' => 'TESTA123',
            'total_amount' => 50000,
            'status' => 'pending',
            'booking_type' => 'online',
        ]);

        $response = $this->actingAs($customerB)
                         ->withHeaders(['Idempotency-Key' => 'test-key-123'])
                         ->postJson("/api/bookings/{$bookingA->id}/checkout");
        
        $response->assertStatus(403)
                 ->assertJsonFragment(['error_code' => 'FORBIDDEN']);

        $this->assertEquals('pending', $bookingA->fresh()->status);
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }

    public function test_customer_can_checkout_own_booking()
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $this->seed();

        $customerA = User::where('email', 'customer@example.com')->first();
        if (!$customerA) {
            $customerA = User::factory()->create(['role' => 'customer']);
        }

        $bookingA = Booking::create([
            'user_id' => $customerA->id,
            'showtime_id' => 1,
            'booking_code' => 'TESTA124',
            'total_amount' => 50000,
            'status' => 'pending',
            'booking_type' => 'online',
        ]);

        $response = $this->actingAs($customerA)
                         ->withHeaders(['Idempotency-Key' => 'test-key-124'])
                         ->postJson("/api/bookings/{$bookingA->id}/checkout");
        $response->assertStatus(200)
                 ->assertJsonStructure(['payment_url', 'idempotency_key']);
                 
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
}
