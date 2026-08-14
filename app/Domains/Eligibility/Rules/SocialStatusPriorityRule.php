<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;

class SocialStatusPriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        return match ($beneficiary->social_status) {
            SocialStatus::WIDOWED => 10,
            SocialStatus::DIVORCED, SocialStatus::SEPARATED => 5,
            default => 0,
        };
    }
}
