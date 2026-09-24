<?php

namespace App\Enums;

enum PeranUser: string
{
    case Superadmin = 'superadmin';
    case AdminSpmb = 'admin_spmb';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Superadmin',
            self::AdminSpmb => 'Admin SPMB',
        };
    }
}
