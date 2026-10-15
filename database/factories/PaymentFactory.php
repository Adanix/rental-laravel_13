<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'type' => PaymentType::RENTAL_AND_DEPOSIT,
            'method' => PaymentMethod::TRANSFER,
            'amount' => 300000,
            'proof_path' => null,
            'status' => PaymentStatus::PENDING,
            'verified_by' => null,
            'verified_at' => null,
            'notes' => null,
        ];
    }
}
