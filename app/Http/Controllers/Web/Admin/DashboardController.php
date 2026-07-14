<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Dashboard stats
        $totalRevenue = DB::selectOne("
            SELECT SUM(p.amount) AS total FROM payments p WHERE p.status = 'success'
        ")->total ?? 0;

        $totalTickets = DB::selectOne("
            SELECT COUNT(*) AS total FROM bookings b WHERE b.status = 'paid'
        ")->total ?? 0;

        $avgOccupancy = DB::selectOne("
            SELECT ROUND(AVG(occupancy), 2) AS avg_rate
            FROM (
                SELECT s.id, SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) * 100.0 / COUNT(ss.id) AS occupancy
                FROM showtimes s
                JOIN showtime_seats ss ON ss.showtime_id = s.id
                WHERE s.start_time BETWEEN NOW() AND NOW() + INTERVAL 7 DAY
                AND s.status != 'cancelled'
                GROUP BY s.id
            ) AS sub
        ")->avg_rate ?? 0;

        // Occupancy for upcoming showtimes
        $upcomingShowtimes = DB::select("
            SELECT
                s.id,
                m.title,
                r.name AS room_name,
                s.start_time,
                COUNT(ss.id) AS total_seats,
                SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) AS booked_seats,
                ROUND(SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) * 100.0 / COUNT(ss.id), 2) AS occupancy_rate
            FROM showtimes s
            JOIN showtime_seats ss ON ss.showtime_id = s.id
            JOIN movies m ON m.id = s.movie_id
            JOIN rooms r ON r.id = s.room_id
            WHERE s.start_time > NOW() AND s.status != 'cancelled'
            GROUP BY s.id, m.title, r.name, s.start_time
            ORDER BY s.start_time ASC
            LIMIT 10
        ");

        return view('admin.dashboard', compact('totalRevenue', 'totalTickets', 'avgOccupancy', 'upcomingShowtimes'));
    }
}
