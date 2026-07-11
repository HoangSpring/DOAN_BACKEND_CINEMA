<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Showtime;
use App\Models\Room;
use App\Models\Movie;
use Illuminate\Support\Facades\Schema;

class ShowtimeOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_showtime_overlap_fails()
    {
        Schema::disableForeignKeyConstraints();
        
        $admin = User::factory()->create(['role' => 'admin']);

        $room = Room::create(['name' => 'Room 1', 'room_type' => '2d']);
        $movie = Movie::create([
            'title' => 'Test Movie',
            'description' => 'Test',
            'duration_minutes' => 120,
            'age_rating' => 'P',
            'status' => 'showing',
            'release_date' => now()->subDays(1)
        ]);

        $startTime = now()->addDays(1)->setHour(10)->setMinute(0);

        $response1 = $this->actingAs($admin)->postJson('/api/admin/showtimes', [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'price_standard' => 50000,
            'price_vip' => 70000
        ]);
        $response1->assertStatus(201);

        $response2 = $this->actingAs($admin)->postJson('/api/admin/showtimes', [
            'movie_id' => $movie->id,
            'room_id' => $room->id,
            'start_time' => $startTime->copy()->addHour()->format('Y-m-d H:i:s'),
            'price_standard' => 50000,
            'price_vip' => 70000
        ]);
        $response2->assertStatus(422)
                 ->assertJsonFragment(['error_code' => 'SHOWTIME_OVERLAP']);
                 
        Schema::enableForeignKeyConstraints();
    }
}
