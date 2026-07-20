@extends('layouts.admin')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-700">Tạo suất chiếu mới</h1>
        <a href="{{ route('admin.showtimes.index') }}" class="text-gray-600 hover:text-gray-900">Quay lại danh sách</a>
    </div>

    {{-- Nút sinh tự động --}}
    <div class="max-w-3xl mx-auto mb-4">
        <button onclick="document.getElementById('auto-modal').classList.remove('hidden')"
            class="w-full px-4 py-3 bg-green-600 text-white rounded font-bold hover:bg-green-500 shadow-lg text-lg flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            ⚡ Sinh suất chiếu tự động cả ngày
        </button>
    </div>

    {{-- Modal sinh tự động --}}
    <div id="auto-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Sinh suất chiếu tự động</h3>
                <button onclick="document.getElementById('auto-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <form action="{{ route('admin.showtimes.auto-generate') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    {{-- Phim --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phim</label>
                        <select name="movie_id"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-green-500 focus:ring-green-500"
                            required>
                            <option value="">-- Chọn phim --</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id }}">{{ $movie->title }} ({{ $movie->duration_minutes }} phút)
                                </option>
                            @endforeach
                        </select>
                        @error('movie_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Phòng --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phòng chiếu</label>
                        <select name="room_id"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-green-500 focus:ring-green-500"
                            required>
                            <option value="">-- Chọn phòng chiếu --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->room_type }})</option>
                            @endforeach
                        </select>
                        @error('room_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Ngày --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ngày chiếu</label>
                        <input type="date" name="date" value="{{ now()->format('Y-m-d') }}"
                            min="{{ now()->format('Y-m-d') }}"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-green-500 focus:ring-green-500"
                            required>
                        @error('date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Khung giờ --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Giờ mở cửa</label>
                            <input type="time" name="start_time" value="09:00"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-green-500 focus:ring-green-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Giờ đóng cửa</label>
                            <input type="time" name="end_time" value="23:00"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-green-500 focus:ring-green-500"
                                required>
                        </div>
                    </div>

                    {{-- Nghỉ giữa suất --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nghỉ giữa các suất (phút)</label>
                        <input type="number" name="gap" value="30" min="0" step="5"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-green-500 focus:ring-green-500"
                            required>
                        <p class="text-xs text-gray-500 mt-1">Thời gian nghỉ giữa 2 suất chiếu liên tiếp</p>
                    </div>

                    {{-- Giá vé --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Standard (VNĐ)</label>
                            <input type="number" name="price_standard" value="70000" min="0" step="1000"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">VIP (VNĐ)</label>
                            <input type="number" name="price_vip" value="90000" min="0" step="1000"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Couple (VNĐ)</label>
                            <input type="number" name="price_couple" value="150000" min="0" step="1000"
                                class="mt-1 block w-full border border-gray-300 rounded-md p-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                    <button type="button" onclick="document.getElementById('auto-modal').classList.add('hidden')"
                        class="px-4 py-2 text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 font-semibold">
                        ⚡ Sinh suất chiếu
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Form tạo thủ công (giữ nguyên) --}}
    <div class="bg-white rounded-lg shadow overflow-hidden p-6 max-w-3xl mx-auto">
        <div class="border-b pb-2 mb-4">
            <h2 class="text-lg font-semibold text-gray-600">Hoặc tạo thủ công từng suất</h2>
        </div>

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