<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowtimeSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'showtime_id',
        'seat_id',
        'status',
        'held_by_user_id',
        'hold_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'hold_expires_at' => 'datetime',
        ];
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function heldBy()
    {
        return $this->belongsTo(User::class, 'held_by_user_id');
    }
}
