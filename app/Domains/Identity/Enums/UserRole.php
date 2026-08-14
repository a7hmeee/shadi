<?php

namespace App\Domains\Identity\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case EMPLOYEE = 'employee';
    case BENEFICIARY = 'beneficiary';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'المدير العام',
            self::ADMIN => 'المدير',
            self::EMPLOYEE => 'الموظف',
            self::BENEFICIARY => 'المستفيد',
        };
    }
}