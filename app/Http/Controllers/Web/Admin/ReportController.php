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

        $revenueData = $this->summarizeChartRows(DB::select("
            SELECT {$selectClause}, SUM(p.amount) AS revenue, COUNT(*) AS total_orders
            FROM payments p
            WHERE p.status = 'success' AND p.paid_at BETWEEN :from AND :to
            GROUP BY {$groupByClause}
            ORDER BY label ASC
        ", [
            'from' => $from . ' 00:00:00',
            'to' => $to . ' 23:59:59'
        ]), 12);

        $revenueByMovie = $this->summarizeChartRows(DB::select("
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
        ]), 10);



        return view('admin.reports.index', compact(
            'from', 'to', 'groupBy', 
            'revenueData', 'revenueByMovie'
        ));
    }

    private function summarizeChartRows(array $rows, int $limit = 10): array
    {
        if (count($rows) <= $limit) {
            return array_map(function ($row) {
                return [
                    'label' => $row->label,
                    'revenue' => (float) ($row->revenue ?? 0),
                    'total_orders' => (int) ($row->total_orders ?? $row->total_bookings ?? 0),
                ];
            }, $rows);
        }

        $topRows = array_slice($rows, 0, $limit - 1);
        $otherRows = array_slice($rows, $limit - 1);

        $summary = [
            'label' => 'Khác',
            'revenue' => 0,
            'total_orders' => 0,
        ];

        foreach ($otherRows as $row) {
            $summary['revenue'] += (float) ($row->revenue ?? 0);
            $summary['total_orders'] += (int) ($row->total_orders ?? $row->total_bookings ?? 0);
        }

        $formattedTopRows = array_map(function ($row) {
            return [
                'label' => $row->label,
                'revenue' => (float) ($row->revenue ?? 0),
                'total_orders' => (int) ($row->total_orders ?? $row->total_bookings ?? 0),
            ];
        }, $topRows);

        $formattedTopRows[] = $summary;

        return $formattedTopRows;
    }

    public function exportRevenueByDate(Request $request)
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

        $rows = DB::select("
            SELECT {$selectClause}, SUM(p.amount) AS revenue, COUNT(*) AS total_orders
            FROM payments p
            WHERE p.status = 'success' AND p.paid_at BETWEEN :from AND :to
            GROUP BY {$groupByClause}
            ORDER BY label ASC
        ", [
            'from' => $from . ' 00:00:00',
            'to' => $to . ' 23:59:59'
        ]);

        return response()->streamDownload(function() use($rows) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(239) . chr(187) . chr(191)); // UTF-8 BOM
            fputcsv($file, ['Ngày/Tháng', 'Doanh thu', 'Tổng số hóa đơn']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->label,
                    $row->revenue,
                    $row->total_orders
                ]);
            }

            fclose($file);
        }, 'doanh_thu_theo_ngay_thang.csv');
    }

    public function exportRevenueByMovie(Request $request)
    {
        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());

        $rows = DB::select("
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

        return response()->streamDownload(function() use($rows) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(239) . chr(187) . chr(191)); // UTF-8 BOM
            fputcsv($file, ['Tên phim', 'Doanh thu', 'Tổng số vé đặt']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->label,
                    $row->revenue,
                    $row->total_bookings
                ]);
            }

            fclose($file);
        }, 'doanh_thu_theo_phim.csv');
    }
}
