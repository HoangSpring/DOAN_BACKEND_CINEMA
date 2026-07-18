<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'room_id',
        'start_time',
        'end_time',
        'price_standard',
        'price_vip',
        'price_couple',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'price_standard' => 'decimal:2',
            'price_vip' => 'decimal:2',
            'price_couple' => 'decimal:2',
        ];
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function showtimeSeats()
    {
        return $this->hasMany(ShowtimeSeat::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
