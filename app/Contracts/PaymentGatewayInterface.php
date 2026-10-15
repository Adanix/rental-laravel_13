<?php

namespace App\Contracts;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * Initiate a new payment record for a booking.
     */
    public function initiatePayment(
        Booking $booking,
        PaymentType $type,
        PaymentMethod $method,
        int $amount,
        ?string $proofPath = null,
        ?string $notes = null
    ): Payment;

    /**
     * Verify payment proof, updating booking status if applicable.
     */
    public function verifyPayment(
        Payment $payment,
        User $actor,
        ?string $notes = null
    ): bool;

    /**
     * Reject payment proof, updating booking status if applicable.
     */
    public function rejectPayment(
        Payment $payment,
        User $actor,
        string $reason
    ): bool;
}
