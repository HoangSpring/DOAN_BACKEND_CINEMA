<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $showtimes = Showtime::with(['movie', 'room'])->get();

        if ($customers->isEmpty() || $showtimes->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($customers, $showtimes) {
            foreach ($showtimes as $showtime) {
                // Generate 5-15 bookings per showtime
                $numBookings = rand(5, 15);

                for ($i = 0; $i < $numBookings; $i++) {
                    $customer = $customers->random();

                    // Randomly select 1 to 4 available seats
                    $numSeats = rand(1, 4);
                    
                    $availableSeats = ShowtimeSeat::where('showtime_id', $showtime->id)
                        ->where('status', 'available')
                        ->with('seat')
                        ->inRandomOrder()
                        ->take($numSeats)
                        ->get();

                    if ($availableSeats->count() < $numSeats) {
                        continue; // Not enough seats, skip this booking
                    }

                    $totalAmount = 0;
                    $bookingSeatsData = [];
                    $seatIds = [];

                    foreach ($availableSeats as $stSeat) {
                        $price = $stSeat->seat->seat_type == 'vip' ? $showtime->price_vip : $showtime->price_standard;
                        if ($stSeat->seat->seat_type == 'couple') {
                            $price = $showtime->price_vip * 1.5; // Example couple price
                        }
                        $totalAmount += $price;

                        $bookingSeatsData[] = [
                            'showtime_seat_id' => $stSeat->id,
                            'price' => $price,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        $seatIds[] = $stSeat->id;
                    }

                    // Create booking
                    $booking = Booking::create([
                        'user_id' => $customer->id,
                        'showtime_id' => $showtime->id,
                        'booking_code' => strtoupper(Str::random(10)),
                        'total_amount' => $totalAmount,
                        'status' => 'paid',
                        'booking_type' => rand(0, 1) ? 'online' : 'counter',
                        'items_data' => json_encode([]),
                        'created_at' => $showtime->start_time->copy()->subDays(rand(1, 5)),
                        'updated_at' => now(),
                    ]);

                    // Insert booking seats
                    foreach ($bookingSeatsData as &$data) {
                        $data['booking_id'] = $booking->id;
                    }
                    DB::table('booking_seats')->insert($bookingSeatsData);

                    // Update showtime seats to booked
                    ShowtimeSeat::whereIn('id', $seatIds)->update(['status' => 'booked']);

                    // Create payment
                    Payment::create([
                        'booking_id' => $booking->id,
                        'amount' => $totalAmount,
                        'payment_method' => $booking->booking_type == 'online' ? 'online_gateway' : 'cash',
                        'transaction_id' => strtoupper(Str::random(15)),
                        'status' => 'success',
                        'idempotency_key' => Str::uuid(),
                        'paid_at' => $booking->created_at->copy()->addMinutes(2),
                        'created_at' => $booking->created_at,
                        'updated_at' => $booking->created_at,
                    ]);
                }
            }
        });
    }
}
