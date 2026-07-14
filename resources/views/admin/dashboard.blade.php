@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-semibold text-gray-700">Dashboard</h1>
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
@endsection
