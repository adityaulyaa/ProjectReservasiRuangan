<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case USER = 'user';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::STAFF => 'Petugas',
            self::USER => 'Pengguna',
        };
    }
}
