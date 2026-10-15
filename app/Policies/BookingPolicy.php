<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isStaff() || $user->id === $booking->user_id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isStaff()) {
            return $booking->status->canCancel();
        }

        // Customer can only cancel before start_at
        return $user->id === $booking->user_id &&
               $booking->status->canCancel() &&
               now()->lt($booking->start_at);
    }

    public function uploadPayment(User $user, Booking $booking): bool
    {
        return $user->isStaff() || ($user->id === $booking->user_id && $booking->status->value === 'pending');
    }

    public function pickup(User $user, Booking $booking): bool
    {
        return $user->isStaff() && $booking->status->canPickup();
    }

    public function return(User $user, Booking $booking): bool
    {
        return $user->isStaff() && $booking->status->canReturn();
    }

    public function verifyPayment(User $user, Booking $booking): bool
    {
        return $user->isStaff();
    }
}
