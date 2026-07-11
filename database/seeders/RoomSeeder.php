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
            ['name' => 'Phòng 1', 'room_type' => '2D', 'rows' => ['A', 'B', 'C', 'D', 'E', 'F'], 'seats_per_row' => 10, 'vip_rows' => ['E', 'F']],
            ['name' => 'Phòng 2', 'room_type' => 'IMAX', 'rows' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], 'seats_per_row' => 12, 'vip_rows' => ['G', 'H']],
            ['name' => 'Phòng 3', 'room_type' => '3D', 'rows' => ['A', 'B', 'C', 'D', 'E'], 'seats_per_row' => 10, 'vip_rows' => ['D', 'E']],
        ];

        DB::transaction(function () use ($rooms) {
            foreach ($rooms as $roomData) {
                $totalSeats = count($roomData['rows']) * $roomData['seats_per_row'];
                $room = Room::create([
                    'name' => $roomData['name'],
                    'room_type' => $roomData['room_type'],
                    'total_seats' => $totalSeats,
                ]);

                $seats = [];
                foreach ($roomData['rows'] as $row) {
                    $type = in_array($row, $roomData['vip_rows']) ? 'vip' : 'standard';
                    for ($i = 1; $i <= $roomData['seats_per_row']; $i++) {
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
