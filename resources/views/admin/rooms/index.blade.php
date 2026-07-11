@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-700">Danh sách phòng chiếu</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($rooms as $room)
        <a href="{{ route('admin.rooms.show', $room->id) }}" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">{{ $room->name }}</h2>
                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded text-xs font-bold">{{ $room->room_type }}</span>
            </div>
            <div class="text-gray-500">
                <span class="font-semibold text-gray-700">{{ $room->seats_count }}</span> ghế ngồi
            </div>
            <div class="mt-4 text-indigo-600 text-sm font-medium flex justify-end">
                Quản lý sơ đồ ghế &rarr;
            </div>
        </a>
    @endforeach
</div>
@endsection
