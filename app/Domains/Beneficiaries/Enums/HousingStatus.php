<?php

namespace App\Domains\Beneficiaries\Enums;

enum HousingStatus: string
{
    case OWNED = 'owned';
    case RENTED = 'rented';
    case HOSTED = 'hosted';
    case TEMPORARY = 'temporary';
    case UNSAFE = 'unsafe';
    case HOMELESS = 'homeless';

    public function label(): string
    {
        return match ($this) {
            self::OWNED => 'ملك',
            self::RENTED => 'إيجار',
            self::HOSTED => 'مستضاف',
            self::TEMPORARY => 'سكن مؤقت',
            self::UNSAFE => 'غير آمن',
            self::HOMELESS => 'بلا مأوى',
        };
    }
}