<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Models\Beneficiary;

class FamilySizePriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        $members = $beneficiary->family_members_count ?? 0;

        return match (true) {
            $members >= 8 => 20,
            $members >= 6 => 15,
            $members >= 4 => 10,
            default => 5,
        };
    }
}
