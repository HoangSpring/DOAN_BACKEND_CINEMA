@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-700">Tạo suất chiếu mới</h1>
        <a href="{{ route('admin.showtimes.index') }}" class="text-gray-600 hover:text-gray-900">Quay lại danh sách</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden p-6 max-w-3xl mx-auto">
        <form action="{{ route('admin.showtimes.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Phim</label>
                    <select name="movie_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required>
                        <option value="">-- Chọn phim --</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}" {{ old('movie_id') == $movie->id ? 'selected' : '' }}>
                                {{ $movie->title }} ({{ $movie->duration_minutes }} phút)
                            </option>
                        @endforeach
                    </select>
                    @error('movie_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Phòng chiếu</label>
                    <select name="room_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required>
                        <option value="">-- Chọn phòng chiếu --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->name }} ({{ $room->room_type }})
                            </option>
                        @endforeach
                    </select>
                    @error('room_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required>
                    @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 md:col-span-1 flex flex-col justify-center text-sm text-gray-500 italic mt-6">
                    * Thời gian kết thúc sẽ được tính tự động: Thời lượng phim + 15 phút dọn phòng.
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Giá ghế Standard (VNĐ)</label>
                    <input type="number" name="price_standard" value="{{ old('price_standard', 70000) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required min="0" step="1000">
                    @error('price_standard') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Giá ghế VIP (VNĐ)</label>
                    <input type="number" name="price_vip" value="{{ old('price_vip', 90000) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required min="0" step="1000">
                    @error('price_vip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Giá ghế Couple (VNĐ)</label>
                    <input type="number" name="price_couple" value="{{ old('price_couple', 150000) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border"
                        required min="0" step="1000">
                    @error('price_couple') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-8 border-t pt-6">
                <button type="submit"
                    class="w-full px-4 py-3 bg-indigo-600 text-white rounded font-bold hover:bg-indigo-500 shadow-lg text-lg">
                    Tạo suất chiếu & Sinh sơ đồ ghế
                </button>
                <p class="text-center text-xs text-gray-500 mt-2">Hệ thống sẽ tự động tạo ghế cho suất chiếu này dựa trên sơ
                    đồ phòng.</p>
            </div>
        </form>
    </div>
@endsection