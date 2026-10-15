<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+5 days');
        $end = (clone $start)->modify('+2 days');

        $subtotal = 200000;
        $deposit = 100000;
        $grandTotal = $subtotal + $deposit;

        return [
            'code' => 'RNT-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'user_id' => User::factory(),
            'start_at' => $start,
            'end_at' => $end,
            'picked_up_at' => null,
            'returned_at' => null,
            'status' => BookingStatus::PENDING,
            'subtotal_rental' => $subtotal,
            'deposit_total' => $deposit,
            'discount_amount' => 0,
            'late_fee' => 0,
            'damage_fee' => 0,
            'grand_total' => $grandTotal,
            'handover_checklist' => null,
            'expires_at' => now()->addHours(2),
        ];
    }
}
