<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'showtime_id',
        'booking_code',
        'total_amount',
        'items_data',
        'status',
        'booking_type',
        'qr_code_data',
        'is_checked_in',
        'checked_in_at',
        'staff_id',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'items_data' => 'array',
            'is_checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function bookingSeats()
    {
        return $this->hasMany(BookingSeat::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
