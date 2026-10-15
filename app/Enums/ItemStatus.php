<?php

namespace App\Enums;

enum ItemStatus: string
{
    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Tersedia / Aktif',
            self::MAINTENANCE => 'Perawatan / Servis',
            self::INACTIVE => 'Nonaktif',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::MAINTENANCE => 'bg-amber-50 text-amber-700 border-amber-200',
            self::INACTIVE => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}
