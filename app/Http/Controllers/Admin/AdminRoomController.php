<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Seat;
use App\Models\ShowtimeSeat;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateSeatRequest;
use App\Http\Resources\RoomResource;
use Illuminate\Support\Facades\DB;

class AdminRoomController extends Controller
{
    public function store(StoreRoomRequest $request)
    {
        $data = $request->validated();
        
        $room = DB::transaction(function () use ($data) {
            $totalSeats = $data['rows'] * $data['seats_per_row'];
            $roomType = $data['room_type'] ?? '2D';
            $vipRows = $data['vip_rows'] ?? [];

            $room = Room::create([
                'name' => $data['name'],
                'room_type' => $roomType,
                'total_seats' => $totalSeats,
            ]);

            $seats = [];
            $alphabet = range('A', 'Z');
            
            for ($i = 0; $i < $data['rows']; $i++) {
                $rowLetter = $alphabet[$i] ?? 'R' . $i; 
                $type = in_array($rowLetter, $vipRows) ? 'vip' : 'standard';
                
                for ($j = 1; $j <= $data['seats_per_row']; $j++) {
                    $seats[] = [
                        'room_id' => $room->id,
                        'seat_row' => $rowLetter,
                        'seat_number' => $j,
                        'seat_type' => $type,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            Seat::insert($seats);

            return $room;
        });

        return response()->json(new RoomResource($room), 201);
    }

    public function updateSeat(UpdateSeatRequest $request, $roomId, $seatId)
    {
        $seat = Seat::where('room_id', $roomId)->findOrFail($seatId);
        
        $hasFutureBookedShowtimes = ShowtimeSeat::where('seat_id', $seatId)
            ->whereIn('status', ['holding', 'booked'])
            ->whereHas('showtime', function ($query) {
                $query->where('start_time', '>', now())->where('status', '!=', 'cancelled');
            })
            ->exists();

        if ($hasFutureBookedShowtimes) {
            return response()->json(['message' => 'Cannot modify seat type, it is already booked or held in future showtimes'], 409);
        }

        $seat->update(['seat_type' => $request->seat_type]);
        
        return response()->json(['message' => 'Seat updated successfully']);
    }
}
