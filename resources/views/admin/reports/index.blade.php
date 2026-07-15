@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-700">Báo cáo & Thống kê</h1>
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
        <div class="flex space-x-2">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500 transition">Thống kê</button>
            <button type="submit" formaction="{{ route('admin.reports.export.date') }}" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-500 transition flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Xuất theo ngày/tháng
            </button>
            <button type="submit" formaction="{{ route('admin.reports.export.movie') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 transition flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Xuất theo phim
            </button>
        </div>
    </form>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Doanh thu theo {{ $groupBy == 'month' ? 'tháng' : 'ngày' }}</h3>
        <div class="h-[320px]">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Doanh thu theo phim</h3>
        <div class="h-[320px]">
            <canvas id="movieRevenueChart"></canvas>
        </div>
    </div>
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
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            maxTicksLimit: 8,
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value);
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxTicksLimit: 8,
                            autoSkip: true
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
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            maxTicksLimit: 8,
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value);
                            }
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false,
                            maxTicksLimit: 10
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
