<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::with('tags');
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        $movies = $query->paginate(10);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $tags = Tag::all();
        return view('admin.movies.create', compact('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'genre' => 'nullable|string|max:100',
            'age_rating' => 'required|in:P,K,T13,T16,T18',
            'status' => 'required|in:showing,coming_soon,ended',
            'release_date' => 'nullable|date',
            'poster_url' => 'nullable|url|max:500',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $movie = Movie::create($data);
        if (!empty($data['tags'])) {
            $movie->tags()->attach($data['tags']);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Thêm phim thành công!');
    }

    public function edit(Movie $movie)
    {
        $tags = Tag::all();
        return view('admin.movies.edit', compact('movie', 'tags'));
    }

    public function update(Request $request, Movie $movie)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'genre' => 'nullable|string|max:100',
            'age_rating' => 'required|in:P,K,T13,T16,T18',
            'status' => 'required|in:showing,coming_soon,ended',
            'release_date' => 'nullable|date',
            'poster_url' => 'nullable|url|max:500',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $movie->update($data);
        if (isset($data['tags'])) {
            $movie->tags()->sync($data['tags']);
        } else {
            $movie->tags()->detach();
        }

        return redirect()->route('admin.movies.index')->with('success', 'Cập nhật phim thành công!');
    }

    public function destroy(Movie $movie)
    {
        $movie->update(['status' => 'ended']);
        return redirect()->route('admin.movies.index')->with('success', 'Đã đánh dấu kết thúc chiếu phim!');
    }
}
