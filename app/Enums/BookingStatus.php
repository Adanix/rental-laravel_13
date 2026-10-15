<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PICKED_UP = 'picked_up';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::CONFIRMED => 'Terkonfirmasi (Siap Diambil)',
            self::PICKED_UP => 'Sedang Disewa',
            self::RETURNED => 'Selesai Dikembalikan',
            self::CANCELLED => 'Dibatalkan Pelanggan',
            self::REJECTED => 'Ditolak Staff',
            self::EXPIRED => 'Kedaluwarsa',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            self::CONFIRMED => 'bg-blue-50 text-blue-700 border-blue-200',
            self::PICKED_UP => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            self::RETURNED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::CANCELLED => 'bg-slate-50 text-slate-600 border-slate-200',
            self::REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
            self::EXPIRED => 'bg-rose-50 text-rose-600 border-rose-200',
        };
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED]);
    }

    public function canPickup(): bool
    {
        return $this === self::CONFIRMED;
    }

    public function canReturn(): bool
    {
        return $this === self::PICKED_UP;
    }
}
