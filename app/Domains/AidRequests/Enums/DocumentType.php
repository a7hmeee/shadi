<?php

namespace App\Domains\AidRequests\Enums;

enum DocumentType: string
{
    case IDENTITY = 'identity';
    case INCOME_PROOF = 'income_proof';
    case MEDICAL_REPORT = 'medical_report';
    case HOUSING_PROOF = 'housing_proof';
    case FAMILY_DOCUMENT = 'family_document';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IDENTITY => 'هوية شخصية',
            self::INCOME_PROOF => 'إثبات دخل',
            self::MEDICAL_REPORT => 'تقرير طبي',
            self::HOUSING_PROOF => 'إثبات سكن',
            self::FAMILY_DOCUMENT => 'وثيقة عائلية',
            self::OTHER => 'أخرى',
        };
    }
}
