<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 2 manual movies
        $movie1 = Movie::create([
            'title' => 'Hành Trình Vũ Trụ',
            'description' => 'Phim khoa học viễn tưởng về hành trình khám phá không gian.',
            'duration_minutes' => 120,
            'genre' => 'Khoa học viễn tưởng',
            'age_rating' => 'T13',
            'status' => 'showing',
            'release_date' => '2026-06-01',
        ]);

        $movie2 = Movie::create([
            'title' => 'Miền Ký Ức',
            'description' => 'Phim tâm lý tình cảm.',
            'duration_minutes' => 105,
            'genre' => 'Tâm lý',
            'age_rating' => 'P',
            'status' => 'showing',
            'release_date' => '2026-06-15',
        ]);

        // attach tags to manual movies
        $movie1->tags()->attach(Tag::whereIn('slug', ['khoa-hoc-vien-tuong', 'hanh-dong', 'dang-hot'])->pluck('id'));
        $movie2->tags()->attach(Tag::whereIn('slug', ['tam-ly', 'de-cu-oscar'])->pluck('id'));

        // 6 factory movies
        $movies = Movie::factory()->count(6)->create();
        
        $tags = Tag::all();
        foreach ($movies as $movie) {
            // attach 1 to 3 random tags
            $movie->tags()->attach($tags->random(rand(1, 3))->pluck('id')->toArray());
        }
    }
}
