<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || in_array($user->role, ['staff', 'admin']);
    }

    /**
     * Determine whether the user can checkout the booking.
     */
    public function checkout(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can checkin the booking.
     */
    public function checkin(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['staff', 'admin']);
    }
}
