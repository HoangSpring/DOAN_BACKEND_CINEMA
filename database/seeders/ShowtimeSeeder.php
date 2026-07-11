<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShowtimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = Movie::where('status', 'showing')->get();
        if ($movies->isEmpty()) {
            $movies = Movie::all();
        }
        $rooms = Room::with('seats')->get();

        if ($movies->isEmpty() || $rooms->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($movies, $rooms) {
            foreach ($movies as $movie) {
                // Generate 2-3 showtimes for each movie
                $numShowtimes = rand(2, 3);

                for ($i = 0; $i < $numShowtimes; $i++) {
                    $room = $rooms->random();
                    
                    // random start time in next 7 days, between 8:00 and 22:00
                    $startHour = rand(8, 22);
                    $startMinute = rand(0, 1) * 30; // 0 or 30
                    $startDate = Carbon::now()->addDays(rand(1, 7))->setTime($startHour, $startMinute);
                    $endDate = $startDate->copy()->addMinutes($movie->duration_minutes + 15); // + 15 mins for cleaning

                    // Check overlap
                    $overlap = Showtime::where('room_id', $room->id)
                        ->where('status', '!=', 'cancelled')
                        ->where('start_time', '<', $endDate)
                        ->where('end_time', '>', $startDate)
                        ->exists();

                    if ($overlap) {
                        $i--; // Retry
                        continue;
                    }

                    $showtime = Showtime::create([
                        'movie_id' => $movie->id,
                        'room_id' => $room->id,
                        'start_time' => $startDate,
                        'end_time' => $endDate,
                        'price_standard' => 80000,
                        'price_vip' => 120000,
                        'status' => 'scheduled',
                    ]);

                    // Bulk insert showtime seats
                    $showtimeSeats = [];
                    foreach ($room->seats as $seat) {
                        $showtimeSeats[] = [
                            'showtime_id' => $showtime->id,
                            'seat_id' => $seat->id,
                            'status' => 'available',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    // Insert in chunks to avoid large packet issues if too many seats
                    foreach (array_chunk($showtimeSeats, 500) as $chunk) {
                        ShowtimeSeat::insert($chunk);
                    }
                }
            }
        });
    }
}
