<?php

namespace App\Enums;

enum FacilityStatus: string
{
    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Aktif',
            self::MAINTENANCE => 'Dalam Perbaikan',
            self::INACTIVE => 'Tidak Aktif',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'green',
            self::MAINTENANCE => 'yellow',
            self::INACTIVE => 'gray',
        };
    }
}
