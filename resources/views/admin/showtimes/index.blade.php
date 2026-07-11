@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-700">Quản lý Suất Chiếu</h1>
    <a href="{{ route('admin.showtimes.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">Thêm suất chiếu mới</a>
</div>

<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" action="{{ route('admin.showtimes.index') }}" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phim</label>
            <select name="movie_id" class="border rounded p-2">
                <option value="">Tất cả phim</option>
                @foreach($movies as $movie)
                    <option value="{{ $movie->id }}" {{ request('movie_id') == $movie->id ? 'selected' : '' }}>{{ $movie->title }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phòng chiếu</label>
            <select name="room_id" class="border rounded p-2">
                <option value="">Tất cả phòng</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày chiếu</label>
            <input type="date" name="date" value="{{ request('date') }}" class="border rounded p-2">
        </div>
        
        <div>
            <button type="submit" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Lọc kết quả</button>
            @if(request('movie_id') || request('room_id') || request('date'))
                <a href="{{ route('admin.showtimes.index') }}" class="ml-2 text-sm text-indigo-600 hover:underline">Xóa lọc</a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full whitespace-nowrap">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Phim</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Phòng chiếu</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Thời gian</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Giá vé (VNĐ)</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Trạng thái</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($showtimes as $showtime)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $showtime->movie->title }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $showtime->room->name }}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($showtime->end_time)->format('H:i') }}
                        </div>
                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($showtime->start_time)->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs text-gray-500">Standard: <span class="text-gray-900 font-medium">{{ number_format($showtime->price_standard, 0, ',', '.') }}</span></div>
                        <div class="text-xs text-gray-500">VIP: <span class="text-gray-900 font-medium">{{ number_format($showtime->price_vip, 0, ',', '.') }}</span></div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($showtime->status == 'scheduled')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Sắp diễn ra</span>
                        @elseif($showtime->status == 'ongoing')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang diễn ra</span>
                        @elseif($showtime->status == 'ended')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Đã kết thúc</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Đã hủy</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Không tìm thấy suất chiếu nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4">
        {{ $showtimes->appends(request()->query())->links() }}
    </div>
</div>
@endsection
