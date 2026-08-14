<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Models\Beneficiary;

class NeedLevelPriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        return match ($beneficiary->need_level) {
            NeedLevel::CRITICAL => 10,
            NeedLevel::HIGH => 7,
            NeedLevel::MEDIUM => 3,
            default => 0,
        };
    }
}
