<?php

namespace App\Enums;

enum PaymentType: string
{
    case RENTAL_AND_DEPOSIT = 'rental_and_deposit';
    case ADDITIONAL_FEE = 'additional_fee';
    case REFUND = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::RENTAL_AND_DEPOSIT => 'Sewa & Deposit',
            self::ADDITIONAL_FEE => 'Denda / Biaya Tambahan',
            self::REFUND => 'Pengembalian Deposit (Refund)',
        };
    }
}
