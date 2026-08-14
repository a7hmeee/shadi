<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Models\Beneficiary;

class IncomePriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        $income = $beneficiary->monthly_income;

        if ($income === null) {
            return 0;
        }

        return match (true) {
            $income <= 500 => 30,
            $income <= 1000 => 25,
            $income <= 1500 => 15,
            $income <= 2000 => 5,
            default => 0,
        };
    }
}
