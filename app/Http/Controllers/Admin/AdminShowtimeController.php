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
use Illuminate\Http\Request;

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

    /**
     * Tự động sinh suất chiếu cả ngày cho 1 phim trong 1 phòng
     */
    public function autoGenerate(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'gap' => 'required|integer|min:0',
            'price_standard' => 'required|integer|min:0',
            'price_vip' => 'required|integer|min:0',
            'price_couple' => 'required|integer|min:0',
        ]);

        $movie = Movie::findOrFail($request->movie_id);
        $room = Room::with('seats')->findOrFail($request->room_id);

        $openTime = Carbon::parse("{$request->date} {$request->start_time}");
        $closeTime = Carbon::parse("{$request->date} {$request->end_time}");
        $gapMinutes = (int) $request->gap;

        // Thời gian 1 suất chiếu = thời lượng phim + 15 phút dọn phòng
        $showDuration = $movie->duration_minutes + 15;

        // Khoảng cách giữa 2 suất liên tiếp = thời lượng suất + thời gian nghỉ
        $slotDuration = $showDuration + $gapMinutes;

        $currentTime = $openTime->copy();
        $count = 0;
        $createdShowtimes = [];

        while (true) {
            $endTime = $currentTime->copy()->addMinutes($showDuration);

            // Nếu suất này vượt quá giờ đóng cửa thì dừng
            if ($endTime->gt($closeTime)) {
                break;
            }

            // Kiểm tra xung đột với TẤT CẢ suất đã có trong cùng phòng
            $conflict = Showtime::where('room_id', $room->id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($currentTime, $endTime) {
                    $query->whereBetween('start_time', [$currentTime, $endTime])
                        ->orWhereBetween('end_time', [$currentTime, $endTime])
                        ->orWhere(function ($q) use ($currentTime, $endTime) {
                            $q->where('start_time', '<=', $currentTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->exists();

            if (!$conflict) {
                DB::transaction(function () use ($movie, $room, $currentTime, $endTime, $request) {
                    $showtime = Showtime::create([
                        'movie_id' => $movie->id,
                        'room_id' => $room->id,
                        'start_time' => $currentTime,
                        'end_time' => $endTime,
                        'price_standard' => $request->price_standard,
                        'price_vip' => $request->price_vip,
                        'price_couple' => $request->price_couple,
                        'status' => 'scheduled',
                    ]);

                    // Tự động tạo ghế cho suất chiếu
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
                });

                $count++;
                $createdShowtimes[] = $currentTime->format('H:i') . ' → ' . $endTime->format('H:i');
            }

            // Di chuyển sang slot tiếp theo (luôn cộng thêm slotDuration dù có tạo được hay không)
            $currentTime->addMinutes($slotDuration);
        }

        if ($count === 0) {
            return redirect()->back()
                ->with('error', 'Không thể tạo suất chiếu nào! Có thể do xung đột thời gian hoặc khung giờ quá ngắn.');
        }

        return redirect()->route('admin.showtimes.index')
            ->with('success', "Đã tạo {$count} suất chiếu: " . implode(', ', $createdShowtimes));
    }
}