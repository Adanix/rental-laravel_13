<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use DomainException;

class TransitionBookingStatusAction
{
    /**
     * Map of legal state transitions.
     */
    protected array $legalTransitions = [
        BookingStatus::PENDING->value => [
            BookingStatus::CONFIRMED->value,
            BookingStatus::CANCELLED->value,
            BookingStatus::REJECTED->value,
            BookingStatus::EXPIRED->value,
        ],
        BookingStatus::CONFIRMED->value => [
            BookingStatus::PICKED_UP->value,
            BookingStatus::CANCELLED->value,
        ],
        BookingStatus::PICKED_UP->value => [
            BookingStatus::RETURNED->value,
        ],
    ];

    /**
     * Transition a booking to a new status.
     *
     * @throws DomainException
     */
    public function execute(
        Booking $booking,
        BookingStatus $newStatus,
        ?User $actor = null,
        ?string $reason = null
    ): Booking {
        $currentStatus = $booking->status;

        if ($currentStatus === $newStatus) {
            return $booking;
        }

        $allowedTargets = $this->legalTransitions[$currentStatus->value] ?? [];

        if (! in_array($newStatus->value, $allowedTargets, true)) {
            throw new DomainException(
                "Transisi status dari '{$currentStatus->label()}' ke '{$newStatus->label()}' tidak diizinkan."
            );
        }

        // Side-effect: Customer cancellation from CONFIRMED creates pending refund payment
        if ($currentStatus === BookingStatus::CONFIRMED && $newStatus === BookingStatus::CANCELLED) {
            $refundPercent = (int) Setting::get('cancellation_refund_percent', 80);
            $refundableAmount = (int) round(($booking->subtotal_rental + $booking->deposit_total) * ($refundPercent / 100));

            Payment::create([
                'booking_id' => $booking->id,
                'type' => PaymentType::REFUND,
                'method' => PaymentMethod::TRANSFER,
                'amount' => $refundableAmount,
                'status' => PaymentStatus::PENDING,
                'notes' => "Refund otomatis pembatalan pelanggan ({$refundPercent}% dari total sewa & deposit)",
            ]);
        }

        $booking->update([
            'status' => $newStatus,
            'expires_at' => in_array($newStatus, [BookingStatus::CONFIRMED, BookingStatus::PICKED_UP, BookingStatus::RETURNED, BookingStatus::CANCELLED, BookingStatus::REJECTED]) ? null : $booking->expires_at,
        ]);

        BookingStatusLog::create([
            'booking_id' => $booking->id,
            'from_status' => $currentStatus,
            'to_status' => $newStatus,
            'actor_id' => $actor?->id,
            'reason' => $reason,
            'created_at' => now(),
        ]);

        // Send queued notification to customer
        $booking->user?->notify(new \App\Notifications\BookingStatusChangedNotification($booking, $newStatus, $reason));

        return $booking->fresh();
    }
}
