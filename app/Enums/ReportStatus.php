<?php

namespace App\Enums;

enum ReportStatus: string
{
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'Baru',
            self::IN_PROGRESS => 'Sedang Diproses',
            self::RESOLVED => 'Selesai',
            self::REJECTED => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::NEW => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::RESOLVED => 'green',
            self::REJECTED => 'red',
        };
    }
}
