<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case TRANSFER = 'transfer';
    case QRIS = 'qris';
    case CASH = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER => 'Transfer Bank Manual',
            self::QRIS => 'QRIS Dinamis / Statis',
            self::CASH => 'Tunai (Di Tempat)',
        };
    }
}
