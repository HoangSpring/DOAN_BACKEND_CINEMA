@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-700">{{ isset($movie) ? 'Sửa phim: ' . $movie->title : 'Thêm phim mới' }}</h1>
    <a href="{{ route('admin.movies.index') }}" class="text-gray-600 hover:text-gray-900">Quay lại</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden p-6">
    <form action="{{ isset($movie) ? route('admin.movies.update', $movie->id) : route('admin.movies.store') }}" method="POST">
        @csrf
        @if(isset($movie))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Tên phim</label>
                <input type="text" name="title" value="{{ old('title', $movie->title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" required>
                @error('title') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">URL Poster</label>
                <input type="url" name="poster_url" value="{{ old('poster_url', $movie->poster_url ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @error('poster_url') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Thời lượng (phút)</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $movie->duration_minutes ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" required min="1">
                @error('duration_minutes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Thể loại</label>
                <input type="text" name="genre" value="{{ old('genre', $movie->genre ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @error('genre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Độ tuổi</label>
                <select name="age_rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                    <option value="P" {{ old('age_rating', $movie->age_rating ?? '') == 'P' ? 'selected' : '' }}>P (Mọi lứa tuổi)</option>
                    <option value="K" {{ old('age_rating', $movie->age_rating ?? '') == 'K' ? 'selected' : '' }}>K (Kèm người lớn)</option>
                    <option value="T13" {{ old('age_rating', $movie->age_rating ?? '') == 'T13' ? 'selected' : '' }}>T13 (Từ 13 tuổi)</option>
                    <option value="T16" {{ old('age_rating', $movie->age_rating ?? '') == 'T16' ? 'selected' : '' }}>T16 (Từ 16 tuổi)</option>
                    <option value="T18" {{ old('age_rating', $movie->age_rating ?? '') == 'T18' ? 'selected' : '' }}>T18 (Từ 18 tuổi)</option>
                </select>
                @error('age_rating') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                    <option value="showing" {{ old('status', $movie->status ?? '') == 'showing' ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="coming_soon" {{ old('status', $movie->status ?? '') == 'coming_soon' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="ended" {{ old('status', $movie->status ?? '') == 'ended' ? 'selected' : '' }}>Đã kết thúc</option>
                </select>
                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="col-span-2 md:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Ngày ra mắt</label>
                <input type="date" name="release_date" value="{{ old('release_date', $movie->release_date ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                @error('release_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tags (Chọn nhiều)</label>
                <select name="tags[]" multiple class="block w-full h-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">
                    @php $selectedTags = isset($movie) ? $movie->tags->pluck('id')->toArray() : old('tags', []); @endphp
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ in_array($tag->id, $selectedTags) ? 'selected' : '' }}>{{ $tag->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều tag.</p>
                @error('tags') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Mô tả</label>
                <textarea name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border">{{ old('description', $movie->description ?? '') }}</textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">Lưu thông tin</button>
        </div>
    </form>
</div>
@endsection
