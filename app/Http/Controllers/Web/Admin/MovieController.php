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
            'director' => 'nullable|string|max:255',
            'actors' => 'nullable|string',
            'content' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'genre' => 'nullable|string|max:100',
            'age_rating' => 'required|in:P,K,T13,T16,T18',
            'status' => 'required|in:showing,coming_soon,ended',
            'release_date' => 'nullable|date',
            'poster_url' => 'nullable|url|max:500',
            'trailer_url' => 'nullable|url|max:500',
            'poster_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'trailer_file' => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($request->hasFile('poster_file')) {
            $path = $request->file('poster_file')->store('posters', 'public');
            $data['poster_url'] = '/storage/' . $path;
        }

        if ($request->hasFile('trailer_file')) {
            $path = $request->file('trailer_file')->store('trailers', 'public');
            $data['trailer_url'] = '/storage/' . $path;
        }

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
            'director' => 'nullable|string|max:255',
            'actors' => 'nullable|string',
            'content' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'genre' => 'nullable|string|max:100',
            'age_rating' => 'required|in:P,K,T13,T16,T18',
            'status' => 'required|in:showing,coming_soon,ended',
            'release_date' => 'nullable|date',
            'poster_url' => 'nullable|url|max:500',
            'trailer_url' => 'nullable|url|max:500',
            'poster_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'trailer_file' => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        if ($request->hasFile('poster_file')) {
            if ($movie->poster_url && str_starts_with($movie->poster_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $movie->poster_url));
            }
            $path = $request->file('poster_file')->store('posters', 'public');
            $data['poster_url'] = '/storage/' . $path;
        }

        if ($request->hasFile('trailer_file')) {
            if ($movie->trailer_url && str_starts_with($movie->trailer_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $movie->trailer_url));
            }
            $path = $request->file('trailer_file')->store('trailers', 'public');
            $data['trailer_url'] = '/storage/' . $path;
        }

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
