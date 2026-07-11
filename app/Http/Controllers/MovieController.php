<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use App\Http\Resources\MovieResource;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::query()->with('tags');

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('tags')) {
            $tags = explode(',', $request->query('tags'));
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        $movies = $query->paginate($request->query('limit', 15));

        return MovieResource::collection($movies);
    }
}
