<?php

namespace App\Domains\Beneficiaries\Enums;

enum NeedLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'منخفض',
            self::MEDIUM => 'متوسط',
            self::HIGH => 'مرتفع',
            self::CRITICAL => 'حرج',
        };
    }
}