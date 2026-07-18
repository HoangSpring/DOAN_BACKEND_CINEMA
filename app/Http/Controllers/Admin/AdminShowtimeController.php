<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Http\Requests\StoreShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AdminShowtimeController extends Controller
{
    public function store(StoreShowtimeRequest $request)
    {
        $data = $request->validated();
        
        $movie = Movie::findOrFail($data['movie_id']);
        $room = Room::with('seats')->findOrFail($data['room_id']);
        
        $startTime = Carbon::parse($data['start_time']);
        $endTime = $startTime->copy()->addMinutes($movie->duration_minutes + 15);

        try {
            $showtime = DB::transaction(function () use ($data, $room, $startTime, $endTime) {
                // Check overlap with lock for update
                $overlap = Showtime::where('room_id', $room->id)
                    ->where('status', '!=', 'cancelled')
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime)
                    ->lockForUpdate()
                    ->first();
                
                if ($overlap) {
                    throw new \Exception('Room already has a showtime at the given time range', 422);
                }

                $showtime = Showtime::create([
                    'movie_id' => $data['movie_id'],
                    'room_id' => $room->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'price_standard' => $data['price_standard'],
                    'price_vip' => $data['price_vip'],
                    'price_couple' => $data['price_couple'],
                    'status' => 'scheduled',
                ]);

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
                
                foreach (array_chunk($showtimeSeats, 500) as $chunk) {
                    ShowtimeSeat::insert($chunk);
                }

                return $showtime;
            });

            return response()->json(new ShowtimeResource($showtime), 201);
        } catch (\Exception $e) {
            if ($e->getCode() == 422) {
                return response()->json([
                    'error_code' => 'SHOWTIME_OVERLAP',
                    'message' => $e->getMessage()
                ], 422);
            }
            throw $e;
        }
    }
}
