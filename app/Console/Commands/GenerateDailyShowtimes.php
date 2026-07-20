<?php
// app/Console/Commands/GenerateDailyShowtimes.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Carbon\Carbon;

class GenerateDailyShowtimes extends Command
{
    protected $signature = 'showtimes:generate 
                            {movie_id : ID của phim} 
                            {room_id : ID của phòng chiếu} 
                            {date : Ngày (YYYY-MM-DD)} 
                            {--start=09:00 : Giờ mở cửa} 
                            {--end=23:00 : Giờ đóng cửa} 
                            {--gap=30 : Phút nghỉ giữa các suất}';

    protected $description = 'Tự động sinh suất chiếu cho 1 phim trong 1 phòng cả ngày';

    public function handle()
    {
        $movie = Movie::findOrFail($this->argument('movie_id'));
        $room = Room::findOrFail($this->argument('room_id'));
        $date = $this->argument('date');

        $openTime = Carbon::parse("{$date} {$this->option('start')}");
        $closeTime = Carbon::parse("{$date} {$this->option('end')}");
        $gapMinutes = (int) $this->option('gap');

        // Thời gian 1 suất = thời lượng phim + dọn phòng (15p) + nghỉ giữa suất
        $slotDuration = $movie->duration_minutes + 15 + $gapMinutes;

        $showtimes = [];
        $currentTime = $openTime->copy();

        while ($currentTime->copy()->addMinutes($movie->duration_minutes + 15)->lte($closeTime)) {
            $endTime = $currentTime->copy()->addMinutes($movie->duration_minutes + 15);

            // Kiểm tra xung đột với suất chiếu khác trong cùng phòng
            $conflict = Showtime::where('room_id', $room->id)
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
                $showtimes[] = [
                    'movie_id' => $movie->id,
                    'room_id' => $room->id,
                    'start_time' => $currentTime->copy(),
                    'end_time' => $endTime,
                    'price_standard' => 70000,
                    'price_vip' => 90000,
                    'price_couple' => 150000,
                ];
            }

            $currentTime->addMinutes($slotDuration);
        }

        if (empty($showtimes)) {
            $this->error('Không thể tạo suất chiếu nào (có thể do xung đột hoặc thời gian không đủ)!');
            return 1;
        }

        foreach ($showtimes as $data) {
            Showtime::create($data);
        }

        $this->info("Đã tạo " . count($showtimes) . " suất chiếu thành công!");
        foreach ($showtimes as $st) {
            $this->line("  - {$st['start_time']->format('H:i')} → {$st['end_time']->format('H:i')}");
        }

        return 0;
    }
}