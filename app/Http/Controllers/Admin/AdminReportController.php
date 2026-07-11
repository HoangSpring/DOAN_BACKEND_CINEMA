<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    public function revenue(Request $request)
    {
        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());
        $groupBy = $request->query('group_by', 'day');

        $selectClause = $groupBy === 'month' 
            ? "DATE_FORMAT(p.paid_at, '%Y-%m') AS month" 
            : "DATE(p.paid_at) AS day";

        $groupByClause = $groupBy === 'month' 
            ? "DATE_FORMAT(p.paid_at, '%Y-%m')" 
            : "DATE(p.paid_at)";

        $orderBy = $groupBy === 'month' ? 'month' : 'day';

        $results = DB::select("
            SELECT {$selectClause}, SUM(p.amount) AS revenue, COUNT(*) AS total_orders
            FROM payments p
            WHERE p.status = 'success' AND p.paid_at BETWEEN :from AND :to
            GROUP BY {$groupByClause}
            ORDER BY {$orderBy} ASC
        ", [
            'from' => $from . ' 00:00:00',
            'to' => $to . ' 23:59:59'
        ]);

        return response()->json($results);
    }

    public function occupancy(Request $request)
    {
        $showtimeId = $request->query('showtime_id');

        if (!$showtimeId) {
            return response()->json(['error' => 'showtime_id is required'], 400);
        }

        $results = DB::select("
            SELECT
                s.id AS showtime_id,
                COUNT(ss.id) AS total_seats,
                SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) AS booked_seats,
                ROUND(SUM(CASE WHEN ss.status = 'booked' THEN 1 ELSE 0 END) * 100.0 / COUNT(ss.id), 2) AS occupancy_rate
            FROM showtimes s
            JOIN showtime_seats ss ON ss.showtime_id = s.id
            WHERE s.id = :showtime_id
            GROUP BY s.id
        ", ['showtime_id' => $showtimeId]);

        return response()->json($results[0] ?? (object)[]);
    }

    public function revenueByMovie()
    {
        $results = DB::select("
            SELECT m.title, SUM(p.amount) AS revenue, COUNT(DISTINCT b.id) AS total_bookings
            FROM payments p
            JOIN bookings b ON b.id = p.booking_id
            JOIN showtimes st ON st.id = b.showtime_id
            JOIN movies m ON m.id = st.movie_id
            WHERE p.status = 'success'
            GROUP BY m.id, m.title
            ORDER BY revenue DESC
        ");

        return response()->json($results);
    }
}
