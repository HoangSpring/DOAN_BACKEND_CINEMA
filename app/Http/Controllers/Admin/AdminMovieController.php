<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Showtime;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieResource;
use Illuminate\Support\Facades\DB;

class AdminMovieController extends Controller
{
    public function store(StoreMovieRequest $request)
    {
        $data = $request->validated();
        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $movie = DB::transaction(function () use ($data, $tagIds) {
            $movie = Movie::create($data);
            if (!empty($tagIds)) {
                $movie->tags()->attach($tagIds);
            }
            return $movie;
        });

        $movie->load('tags');
        return response()->json(new MovieResource($movie), 201);
    }

    public function update(UpdateMovieRequest $request, $id)
    {
        $movie = Movie::findOrFail($id);
        $data = $request->validated();
        $tagIds = $data['tag_ids'] ?? null;
        unset($data['tag_ids']);

        if (isset($data['duration_minutes']) && $data['duration_minutes'] != $movie->duration_minutes) {
            $hasFutureShowtimes = Showtime::where('movie_id', $movie->id)
                ->where('start_time', '>', now())
                ->where('status', '!=', 'cancelled')
                ->exists();
            
            if ($hasFutureShowtimes) {
                return response()->json(['message' => 'Cannot change duration, movie has future showtimes'], 409);
            }
        }

        DB::transaction(function () use ($movie, $data, $tagIds) {
            $movie->update($data);
            if ($tagIds !== null) {
                $movie->tags()->sync($tagIds);
            }
        });

        $movie->load('tags');
        return response()->json(new MovieResource($movie));
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        
        $hasFutureShowtimes = Showtime::where('movie_id', $movie->id)
            ->where('start_time', '>', now())
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($hasFutureShowtimes) {
            return response()->json(['message' => 'Cannot delete movie, it has future showtimes'], 409);
        }

        $movie->update(['status' => 'ended']);
        return response()->json(null, 204);
    }
}
