<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Showtime;
use App\Models\Movie;
use App\Models\Room;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        $query = Showtime::with(['movie', 'room'])->orderBy('start_time', 'desc');
        
        if ($request->has('movie_id') && $request->movie_id) {
            $query->where('movie_id', $request->movie_id);
        }
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->has('date') && $request->date) {
            $query->whereDate('start_time', $request->date);
        }
        
        $showtimes = $query->paginate(15);
        $movies = Movie::all();
        $rooms = Room::all();
        
        return view('admin.showtimes.index', compact('showtimes', 'movies', 'rooms'));
    }

    public function create()
    {
        $movies = Movie::where('status', 'showing')->get();
        $rooms = Room::all();
        return view('admin.showtimes.create', compact('movies', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date|after:now',
            'price_standard' => 'required|numeric|min:0',
            'price_vip' => 'required|numeric|min:0',
        ]);

        $movie = Movie::findOrFail($data['movie_id']);
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($movie->duration_minutes + 15); // + 15m buffer

        try {
            DB::beginTransaction();

            // Lock rooms for update to prevent overlap race conditions
            $overlap = Showtime::where('room_id', $data['room_id'])
                ->where('status', '!=', 'cancelled')
                ->where(function($q) use ($startTime, $endTime) {
                    $q->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function($q2) use ($startTime, $endTime) {
                          $q2->where('start_time', '<=', $startTime)
                             ->where('end_time', '>=', $endTime);
                      });
                })->lockForUpdate()->exists();

            if ($overlap) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Phòng đã có suất chiếu trùng giờ.');
            }

            $showtime = Showtime::create([
                'movie_id' => $data['movie_id'],
                'room_id' => $data['room_id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'price_standard' => $data['price_standard'],
                'price_vip' => $data['price_vip'],
                'status' => 'scheduled'
            ]);

            // Bulk insert showtime_seats
            $seats = \App\Models\Seat::where('room_id', $data['room_id'])->get();
            $showtimeSeats = $seats->map(function($seat) use ($showtime) {
                return [
                    'showtime_id' => $showtime->id,
                    'seat_id' => $seat->id,
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });
            \App\Models\ShowtimeSeat::insert($showtimeSeats->toArray());

            DB::commit();

            return redirect()->route('admin.showtimes.index')->with('success', 'Tạo suất chiếu thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }
}
