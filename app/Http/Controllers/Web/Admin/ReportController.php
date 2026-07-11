<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());
        $groupBy = $request->query('group_by', 'day');

        $selectClause = $groupBy === 'month' 
            ? "DATE_FORMAT(p.paid_at, '%Y-%m') AS label" 
            : "DATE(p.paid_at) AS label";

        $groupByClause = $groupBy === 'month' 
            ? "DATE_FORMAT(p.paid_at, '%Y-%m')" 
            : "DATE(p.paid_at)";

        $revenueData = DB::select("
            SELECT {$selectClause}, SUM(p.amount) AS revenue, COUNT(*) AS total_orders
            FROM payments p
            WHERE p.status = 'success' AND p.paid_at BETWEEN :from AND :to
            GROUP BY {$groupByClause}
            ORDER BY label ASC
        ", [
            'from' => $from . ' 00:00:00',
            'to' => $to . ' 23:59:59'
        ]);

        $revenueByMovie = DB::select("
            SELECT m.title AS label, SUM(p.amount) AS revenue, COUNT(DISTINCT b.id) AS total_bookings
            FROM payments p
            JOIN bookings b ON b.id = p.booking_id
            JOIN showtimes st ON st.id = b.showtime_id
            JOIN movies m ON m.id = st.movie_id
            WHERE p.status = 'success' AND p.paid_at BETWEEN :from AND :to
            GROUP BY m.id, m.title
            ORDER BY revenue DESC
        ", [
            'from' => $from . ' 00:00:00',
            'to' => $to . ' 23:59:59'
        ]);

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
                WHERE s.status = 'ended'
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

        return view('admin.reports.index', compact(
            'from', 'to', 'groupBy', 
            'revenueData', 'revenueByMovie', 
            'totalRevenue', 'totalTickets', 'avgOccupancy',
            'upcomingShowtimes'
        ));
    }
}
