<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            ['name' => 'Phòng 1', 'room_type' => '2D', 'rows' => ['A', 'B', 'C', 'D', 'E', 'F', 'G'], 'seats_per_row' => 10, 'vip_rows' => ['E', 'F'], 'couple_rows' => ['G']],
            ['name' => 'Phòng 2', 'room_type' => 'IMAX', 'rows' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'], 'seats_per_row' => 12, 'vip_rows' => ['G', 'H'], 'couple_rows' => ['I']],
            ['name' => 'Phòng 3', 'room_type' => '3D', 'rows' => ['A', 'B', 'C', 'D', 'E', 'F'], 'seats_per_row' => 10, 'vip_rows' => ['D', 'E'], 'couple_rows' => ['F']],
            ['name' => 'Phòng 4', 'room_type' => '2D', 'rows' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], 'seats_per_row' => 10, 'vip_rows' => ['D', 'E', 'F'], 'couple_rows' => ['G', 'H']],
            ['name' => 'Phòng 5', 'room_type' => '2D', 'rows' => ['A', 'B', 'C', 'D', 'E'], 'seats_per_row' => 8, 'vip_rows' => ['B', 'C', 'D'], 'couple_rows' => ['E']],
        ];

        DB::transaction(function () use ($rooms) {
            foreach ($rooms as $roomData) {
                $totalSeats = 0;
                foreach ($roomData['rows'] as $r) {
                    $t = 'standard';
                    if (in_array($r, $roomData['vip_rows'] ?? [])) $t = 'vip';
                    if (in_array($r, $roomData['couple_rows'] ?? [])) $t = 'couple';
                    $totalSeats += ($t === 'couple') ? ($roomData['seats_per_row'] / 2) : $roomData['seats_per_row'];
                }

                $room = Room::create([
                    'name' => $roomData['name'],
                    'room_type' => $roomData['room_type'],
                    'total_seats' => $totalSeats,
                ]);

                $seats = [];
                foreach ($roomData['rows'] as $row) {
                    $type = 'standard';
                    if (in_array($row, $roomData['vip_rows'] ?? [])) $type = 'vip';
                    if (in_array($row, $roomData['couple_rows'] ?? [])) $type = 'couple';
                    
                    $numSeats = $type === 'couple' ? ($roomData['seats_per_row'] / 2) : $roomData['seats_per_row'];
                    for ($i = 1; $i <= $numSeats; $i++) {
                        $seats[] = [
                            'room_id' => $room->id,
                            'seat_row' => $row,
                            'seat_number' => $i,
                            'seat_type' => $type,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                Seat::insert($seats);
            }
        });
    }
}
