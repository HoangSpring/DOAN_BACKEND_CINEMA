<?php

namespace Tests\Feature;

use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_detail_page_shows_director_actors_and_content()
    {
        $movie = Movie::create([
            'title' => 'Test Movie',
            'description' => 'A short teaser description.',
            'content' => 'A full synopsis about the movie.',
            'director' => 'Jane Doe',
            'actors' => 'John Smith, Mary Johnson',
            'duration_minutes' => 120,
            'genre' => 'Drama',
            'age_rating' => 'T13',
            'status' => 'showing',
        ]);

        $response = $this->get(route('movies.show', ['movie' => $movie->id]));

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee('John Smith, Mary Johnson');
        $response->assertSee('A full synopsis about the movie.');
    }
}
