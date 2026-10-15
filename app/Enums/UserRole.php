<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff Operasional',
            self::CUSTOMER => 'Pelanggan',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isStaff(): bool
    {
        return $this === self::STAFF || $this === self::ADMIN;
    }

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }
}
