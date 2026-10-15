<?php

namespace App\Services;

use App\Actions\TransitionBookingStatusAction;
use App\Contracts\PaymentGatewayInterface;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;

class ManualPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        protected TransitionBookingStatusAction $transitionBookingStatusAction
    ) {}

    public function initiatePayment(
        Booking $booking,
        PaymentType $type,
        PaymentMethod $method,
        int $amount,
        ?string $proofPath = null,
        ?string $notes = null
    ): Payment {
        return Payment::create([
            'booking_id' => $booking->id,
            'type' => $type,
            'method' => $method,
            'amount' => $amount,
            'proof_path' => $proofPath,
            'status' => PaymentStatus::PENDING,
            'notes' => $notes,
        ]);
    }

    public function verifyPayment(
        Payment $payment,
        User $actor,
        ?string $notes = null
    ): bool {
        $payment->update([
            'status' => PaymentStatus::VERIFIED,
            'verified_by' => $actor->id,
            'verified_at' => now(),
            'notes' => $notes ?: $payment->notes,
        ]);

        $booking = $payment->booking;

        // Auto transition booking pending -> confirmed upon verifying rental_and_deposit
        if ($payment->type === PaymentType::RENTAL_AND_DEPOSIT && $booking->status === BookingStatus::PENDING) {
            $this->transitionBookingStatusAction->execute(
                booking: $booking,
                newStatus: BookingStatus::CONFIRMED,
                actor: $actor,
                reason: 'Pembayaran telah diverifikasi sah oleh staff (' . $actor->name . ')'
            );
        }

        return true;
    }

    public function rejectPayment(
        Payment $payment,
        User $actor,
        string $reason
    ): bool {
        $payment->update([
            'status' => PaymentStatus::REJECTED,
            'verified_by' => $actor->id,
            'verified_at' => now(),
            'notes' => 'Ditolak: ' . $reason,
        ]);

        $booking = $payment->booking;

        // If proof of payment is rejected, transition booking to rejected to free stock immediately
        if ($payment->type === PaymentType::RENTAL_AND_DEPOSIT && $booking->status === BookingStatus::PENDING) {
            $this->transitionBookingStatusAction->execute(
                booking: $booking,
                newStatus: BookingStatus::REJECTED,
                actor: $actor,
                reason: 'Bukti pembayaran ditolak oleh staff: ' . $reason
            );
        }

        return true;
    }
}
