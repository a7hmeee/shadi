<?php

namespace App\Domains\AidRequests\Enums;

enum AidRequestStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case NEEDS_INFORMATION = 'needs_information';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::SUBMITTED => 'مقدم',
            self::UNDER_REVIEW => 'قيد المراجعة',
            self::NEEDS_INFORMATION => 'بحاجة إلى معلومات',
            self::APPROVED => 'مقبول',
            self::REJECTED => 'مرفوض',
            self::DELIVERED => 'تم التسليم',
            self::CANCELLED => 'ملغي',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::REJECTED,
            self::DELIVERED,
            self::CANCELLED,
        ]);
    }

    public function canBeEditedByBeneficiary(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::NEEDS_INFORMATION,
        ]);
    }
}