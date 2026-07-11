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

        return ShowtimeResource::collection($query->get());
    }

    public function seats(Showtime $showtime)
    {
        $seats = $showtime->showtimeSeats()->with('seat')->get();
        return ShowtimeSeatResource::collection($seats);
    }
}
