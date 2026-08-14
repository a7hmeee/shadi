<?php

namespace App\Domains\AidRequests\Enums;

enum AidType: string
{
    case FOOD = 'food';
    case CASH = 'cash';
    case MEDICAL = 'medical';
    case HOUSING = 'housing';
    case EDUCATION = 'education';
    case CLOTHING = 'clothing';
    case EMERGENCY = 'emergency';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FOOD => 'مساعدة غذائية',
            self::CASH => 'مساعدة نقدية',
            self::MEDICAL => 'مساعدة طبية',
            self::HOUSING => 'مساعدة سكن',
            self::EDUCATION => 'مساعدة تعليمية',
            self::CLOTHING => 'ملابس',
            self::EMERGENCY => 'مساعدة طارئة',
            self::OTHER => 'أخرى',
        };
    }
}