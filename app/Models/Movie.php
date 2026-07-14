<?php

namespace App\Models;

use App\Models\Showtime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'director',
        'actors',
        'content',
        'duration_minutes',
        'genre',
        'age_rating',
        'poster_url',
        'trailer_url',
        'status',
        'release_date',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'movie_tags', 'movie_id', 'tag_id');
    }

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
}
