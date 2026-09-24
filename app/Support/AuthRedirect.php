<?php

namespace App\Support;

use App\Enums\Role;

class AuthRedirect
{
    public static function getDashboardForRole(Role $role): string
    {
        return match ($role->value) {
            Role::ADMIN->value => route('admin.dashboard', absolute: false),
            Role::STAFF->value => route('staff.dashboard', absolute: false),
            default => route('dashboard', absolute: false),
        };
    }
}
