@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-700">Báo cáo & Thống kê</h1>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-2">Tổng doanh thu</h3>
        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', '.') }} ₫</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-2">Vé đã bán</h3>
        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalTickets) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-2">Tỷ lệ lấp đầy TB</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $avgOccupancy }}%</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow p-4 mb-8">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Từ ngày</label>
            <input type="date" name="from" value="{{ $from }}" class="border rounded p-2 focus:ring focus:ring-indigo-200 focus:border-indigo-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Đến ngày</label>
            <input type="date" name="to" value="{{ $to }}" class="border rounded p-2 focus:ring focus:ring-indigo-200 focus:border-indigo-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nhóm theo</label>
            <select name="group_by" class="border rounded p-2 focus:ring focus:ring-indigo-200 focus:border-indigo-300">
                <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Ngày</option>
                <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Tháng</option>
            </select>
        </div>
        <div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500 transition">Thống kê</button>
        </div>
    </form>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Doanh thu theo {{ $groupBy == 'month' ? 'tháng' : 'ngày' }}</h3>
        <canvas id="revenueChart" height="250"></canvas>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Doanh thu theo phim</h3>
        <canvas id="movieRevenueChart" height="250"></canvas>
    </div>
</div>

<!-- Occupancy Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Tỷ lệ lấp đầy suất chiếu sắp diễn ra</h3>
    </div>
    <table class="w-full whitespace-nowrap">
        <thead class="bg-gray-50 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suất chiếu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lấp đầy</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi tiết</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($upcomingShowtimes as $st)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $st->title }}</div>
                        <div class="text-xs text-gray-500">{{ $st->room_name }} - {{ \Carbon\Carbon::parse($st->start_time)->format('H:i d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <span class="mr-2 text-sm text-gray-900 font-semibold w-10">{{ $st->occupancy_rate }}%</span>
                            <div class="w-48 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $st->occupancy_rate > 80 ? 'bg-green-500' : ($st->occupancy_rate > 40 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $st->occupancy_rate }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $st->booked_seats }} / {{ $st->total_seats }} ghế
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Không có suất chiếu nào sắp diễn ra.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for Revenue Chart
        const revenueDataRaw = @json($revenueData);
        const revenueLabels = revenueDataRaw.map(item => item.label);
        const revenueValues = revenueDataRaw.map(item => item.revenue);

        const ctx1 = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenueValues,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                            }
                        }
                    }
                }
            }
        });

        // Data for Movie Revenue Chart
        const movieRevenueRaw = @json($revenueByMovie);
        const movieLabels = movieRevenueRaw.map(item => item.label);
        const movieValues = movieRevenueRaw.map(item => item.revenue);

        const ctx2 = document.getElementById('movieRevenueChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: movieLabels,
                datasets: [{
                    label: 'Doanh thu',
                    data: movieValues,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal bar chart
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN').format(context.raw) + ' ₫';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
