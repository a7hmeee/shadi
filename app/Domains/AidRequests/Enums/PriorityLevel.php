<?php

namespace App\Domains\AidRequests\Enums;

enum PriorityLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'منخفضة',
            self::MEDIUM => 'متوسطة',
            self::HIGH => 'مرتفعة',
            self::CRITICAL => 'حرجة',
        };
    }
}