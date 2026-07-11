<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Seat;
use App\Models\ShowtimeSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::withCount('seats')->get();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function show(Room $room)
    {
        $seats = $room->seats()->orderBy('seat_row')->orderBy('seat_number')->get();
        return view('admin.rooms.show', compact('room', 'seats'));
    }

    public function updateSeat(Request $request, Room $room, Seat $seat)
    {
        if ($seat->room_id !== $room->id) abort(404);

        $request->validate([
            'seat_type' => 'required|in:standard,vip,couple'
        ]);

        // Validate overlap checking for future bookings as per prompt instructions
        $hasFutureBookings = ShowtimeSeat::where('seat_id', $seat->id)
            ->whereHas('showtime', function($q) {
                $q->where('start_time', '>', now());
            })
            ->where('status', '!=', 'available')
            ->exists();

        if ($hasFutureBookings) {
            return response()->json(['error' => 'Ghế thuộc suất chiếu tương lai đã có người đặt.'], 409);
        }

        $seat->update(['seat_type' => $request->seat_type]);
        
        return response()->json(['success' => true, 'seat' => $seat]);
    }
}
