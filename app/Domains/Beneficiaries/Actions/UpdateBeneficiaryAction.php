<?php

namespace App\Domains\Beneficiaries\Actions;

use App\Domains\Beneficiaries\DTOs\BeneficiaryData;
use App\Domains\Beneficiaries\Models\Beneficiary;

class UpdateBeneficiaryAction
{
    public function execute(Beneficiary $beneficiary, BeneficiaryData $data): Beneficiary
    {
        $beneficiary->update($data->toArray());

        return $beneficiary->fresh();
    }
}