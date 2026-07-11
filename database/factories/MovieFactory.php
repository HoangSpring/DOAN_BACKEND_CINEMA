<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['showing', 'coming_soon', 'ended'];
        $ratings = ['P', 'K', 'T13', 'T16', 'T18'];
        $genres = ['Hành động', 'Khoa học viễn tưởng', 'Tâm lý', 'Hoạt hình', 'Hài hước', 'Kinh dị'];
        
        return [
            'title' => 'Phim ' . fake()->words(3, true),
            'description' => fake()->realText(),
            'duration_minutes' => fake()->numberBetween(90, 150),
            'genre' => fake()->randomElement($genres),
            'age_rating' => fake()->randomElement($ratings),
            'poster_url' => fake()->imageUrl(600, 800, 'movie'),
            'status' => fake()->randomElement($statuses),
            'release_date' => Carbon::now()->addDays(fake()->numberBetween(-30, 30))->format('Y-m-d'),
        ];
    }
}
