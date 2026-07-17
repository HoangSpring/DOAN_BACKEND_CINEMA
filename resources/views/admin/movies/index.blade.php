@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-700">Quản lý Phim</h1>
    <a href="{{ route('admin.movies.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">Thêm phim mới</a>
</div>

<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" action="{{ route('admin.movies.index') }}" class="flex flex-wrap md:flex-nowrap gap-2">
        <input type="text" name="search" placeholder="Tìm kiếm phim..." value="{{ request('search') }}" class="flex-grow px-4 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 min-w-[200px]">
        
        <select name="duration" class="px-4 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700">
            <option value="">Tất cả thời lượng</option>
            <option value="short" {{ request('duration') == 'short' ? 'selected' : '' }}>Dưới 90 phút</option>
            <option value="medium" {{ request('duration') == 'medium' ? 'selected' : '' }}>90 - 120 phút</option>
            <option value="long" {{ request('duration') == 'long' ? 'selected' : '' }}>Trên 120 phút</option>
        </select>
        
        <select name="age_rating" class="px-4 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700">
            <option value="">Tất cả phân loại</option>
            <option value="P" {{ request('age_rating') == 'P' ? 'selected' : '' }}>P (Mọi độ tuổi)</option>
            <option value="K" {{ request('age_rating') == 'K' ? 'selected' : '' }}>K (Dưới 13T có người lớn)</option>
            <option value="T13" {{ request('age_rating') == 'T13' ? 'selected' : '' }}>T13 (Từ 13T)</option>
            <option value="T16" {{ request('age_rating') == 'T16' ? 'selected' : '' }}>T16 (Từ 16T)</option>
            <option value="T18" {{ request('age_rating') == 'T18' ? 'selected' : '' }}>T18 (Từ 18T)</option>
        </select>
        
        <select name="status" class="px-4 py-2 border rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-700">
            <option value="">Tất cả trạng thái</option>
            <option value="showing" {{ request('status') == 'showing' ? 'selected' : '' }}>Đang chiếu</option>
            <option value="coming_soon" {{ request('status') == 'coming_soon' ? 'selected' : '' }}>Sắp chiếu</option>
            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
        </select>
        
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500 whitespace-nowrap">Lọc</button>
        <a href="{{ route('admin.movies.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 whitespace-nowrap">Xóa</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full whitespace-nowrap">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Phim</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Thời lượng</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Phân loại</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                <th class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($movies as $movie)
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($movie->poster_url)
                                <img src="{{ $movie->poster_url }}" class="h-10 w-10 object-cover rounded mr-3">
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $movie->title }}</div>
                                <div class="text-sm text-gray-500">
                                    @foreach($movie->tags as $tag)
                                        <span class="inline-flex bg-gray-100 text-xs px-2 py-0.5 rounded">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $movie->duration_minutes }} phút</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $movie->age_rating }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($movie->status == 'showing')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đang chiếu</span>
                        @elseif($movie->status == 'coming_soon')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Sắp chiếu</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Đã kết thúc</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium" x-data="{ openDelete: false }">
                        <a href="{{ route('admin.movies.edit', $movie->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</a>
                        
                        <button @click="openDelete = true" class="text-red-600 hover:text-red-900">Xóa</button>
                        
                        <!-- Delete Modal -->
                        <div x-show="openDelete" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="openDelete" class="fixed inset-0 transition-opacity" aria-hidden="true">
                                    <div class="absolute inset-0 bg-gray-500 opacity-75" @click="openDelete = false"></div>
                                </div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div x-show="openDelete" class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Kết thúc chiếu phim</h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">Bạn có chắc chắn muốn kết thúc chiếu phim "{{ $movie->title }}"? Phim này sẽ không còn hiển thị cho người đặt vé.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <form method="POST" action="{{ route('admin.movies.destroy', $movie->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Đồng ý</button>
                                        </form>
                                        <button @click="openDelete = false" type="button" class="mt-3 inline-flex justify-center w-full px-4 py-2 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Hủy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4">
        {{ $movies->links() }}
    </div>
</div>
@endsection
