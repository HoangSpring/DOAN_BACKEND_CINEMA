<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\Http\Request;
use App\Http\Resources\ShowtimeResource;
use App\Http\Resources\ShowtimeSeatResource;

class ShowtimeController extends Controller
{
    public function index(Request $request, Movie $movie)
    {
        $query = $movie->showtimes()->where('status', 'scheduled');

        if ($request->has('date')) {
            $date = $request->query('date');
            $query->whereDate('start_time', $date);
        }

        // Luôn giới hạn không lấy các suất chiếu đã trôi qua hơn 30 phút (kể cả chọn ngày cũ)
        $query->where('start_time', '>=', now()->subMinutes(30));

        return ShowtimeResource::collection($query->get());
    }

    public function seats(Showtime $showtime)
    {
        $seats = $showtime->showtimeSeats()->with('seat')->get();
        return ShowtimeSeatResource::collection($seats);
    }
}
