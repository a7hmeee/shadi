<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Models\Beneficiary;

class DisabilityPriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        return $beneficiary->has_disability ? 15 : 0;
    }
}
