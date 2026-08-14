<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Models\Beneficiary;

class ChronicDiseasePriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        return $beneficiary->has_chronic_disease ? 10 : 0;
    }
}
