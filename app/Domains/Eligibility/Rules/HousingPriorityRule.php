<?php

namespace App\Domains\Eligibility\Rules;

use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;

class HousingPriorityRule
{
    public function score(Beneficiary $beneficiary): int
    {
        return match ($beneficiary->housing_status) {
            HousingStatus::UNSAFE, HousingStatus::HOMELESS => 10,
            HousingStatus::TEMPORARY => 7,
            HousingStatus::HOSTED => 5,
            HousingStatus::RENTED => 3,
            default => 0,
        };
    }
}
