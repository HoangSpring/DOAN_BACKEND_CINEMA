<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'seat_row',
        'seat_number',
        'seat_type',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
