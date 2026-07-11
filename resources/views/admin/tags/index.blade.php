@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-700">Quản lý Tags</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full whitespace-nowrap">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Tên Tag</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Slug</th>
                        <th class="px-6 py-3 text-right text-sm font-medium text-gray-500 uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($tags as $tag)
                        <tr x-data="{ editing: false }">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                <span x-show="!editing">{{ $tag->name }}</span>
                                <input type="text" x-show="editing" form="edit-form-{{ $tag->id }}" name="name" value="{{ $tag->name }}" class="border rounded p-1 w-full">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <span x-show="!editing">{{ $tag->slug }}</span>
                                <input type="text" x-show="editing" form="edit-form-{{ $tag->id }}" name="slug" value="{{ $tag->slug }}" class="border rounded p-1 w-full">
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <form id="edit-form-{{ $tag->id }}" action="{{ route('admin.tags.update', $tag->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('PUT')
                                </form>

                                <button @click="editing = true" x-show="!editing" class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</button>
                                <button type="submit" form="edit-form-{{ $tag->id }}" x-show="editing" class="text-green-600 hover:text-green-900 mr-3">Lưu</button>
                                <button @click="editing = false" x-show="editing" class="text-gray-600 hover:text-gray-900 mr-3">Hủy</button>
                                
                                <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa tag này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="md:col-span-1">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Thêm Tag Mới</h2>
            <form action="{{ route('admin.tags.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tên Tag</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" required>
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Slug (Tùy chọn)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2 border" placeholder="Tu dong tao neu de trong">
                    @error('slug') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500">Thêm Tag</button>
            </form>
        </div>
    </div>
</div>
@endsection
