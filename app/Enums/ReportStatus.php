<?php

namespace App\Enums;

enum ReportStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case UNDER_REPAIR = 'under_repair';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::IN_PROGRESS => 'Sedang Diproses',
            self::UNDER_REPAIR => 'Sedang Diperbaiki',
            self::RESOLVED => 'Selesai',
            self::REJECTED => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::UNDER_REPAIR => 'orange',
            self::RESOLVED => 'green',
            self::REJECTED => 'red',
        };
    }
}
