<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Showtime;
use App\Models\Movie;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function counter(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        $movieId = $request->query('movie_id');
        
        $query = Showtime::with(['movie', 'room'])
            ->whereDate('start_time', $date)
            ->where('status', 'scheduled');

        // Luôn loại bỏ tất cả các suất chiếu đã qua hơn 30 phút (chặn luôn cả ngày hôm qua)
        $query->where('start_time', '>=', Carbon::now()->subMinutes(30));

        if ($movieId) {
            $query->where('movie_id', $movieId);
        }
            
        $showtimes = $query->orderBy('start_time', 'asc')->get();

        // Lấy tất cả các phim (kể cả đã hết suất) để hiển thị vào bộ lọc dropdown
        $movies = Movie::orderBy('title', 'asc')->get();
            
        $showtimeId = $request->query('showtime_id');
        $selectedShowtime = null;
        $seats = null;
        
        if ($showtimeId) {
            $selectedShowtime = Showtime::with(['movie', 'room'])->findOrFail($showtimeId);
            $seats = \App\Models\ShowtimeSeat::with('seat')
                ->where('showtime_id', $showtimeId)
                ->get()
                ->map(function ($ss) {
                    $status = $ss->status;
                    if ($status === 'holding' && $ss->hold_expires_at && $ss->hold_expires_at->isPast()) {
                        $status = 'available';
                    }

                    $seatType = $ss->seat?->seat_type ?? 'standard';

                    return [
                        'id' => $ss->id,
                        'seat_id' => $ss->seat_id,
                        'row' => $ss->seat?->seat_row,
                        'seat_row' => $ss->seat?->seat_row,
                        'number' => $ss->seat?->seat_number,
                        'seat_number' => $ss->seat?->seat_number,
                        'type' => $seatType,
                        'seat_type' => $seatType,
                        'status' => $status,
                        'price' => $seatType === 'vip' ? $ss->showtime->price_vip : $ss->showtime->price_standard,
                    ];
                });
        }
        
        return view('staff.counter', compact('date', 'showtimes', 'selectedShowtime', 'seats', 'movies'));
    }

    public function checkin()
    {
        return view('staff.checkin');
    }
}
