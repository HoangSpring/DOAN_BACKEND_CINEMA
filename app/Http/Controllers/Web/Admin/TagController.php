<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:tags',
            'slug' => 'nullable|string|max:120|unique:tags',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        Tag::create($data);
        return redirect()->route('admin.tags.index')->with('success', 'Thêm tag thành công!');
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:tags,name,' . $tag->id,
            'slug' => 'nullable|string|max:120|unique:tags,slug,' . $tag->id,
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $tag->update($data);
        return redirect()->route('admin.tags.index')->with('success', 'Cập nhật tag thành công!');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('admin.tags.index')->with('success', 'Xóa tag thành công!');
    }
}
