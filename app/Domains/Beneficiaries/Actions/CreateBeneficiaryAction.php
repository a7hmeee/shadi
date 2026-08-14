<?php

namespace App\Domains\Beneficiaries\Actions;

use App\Domains\Beneficiaries\DTOs\BeneficiaryData;
use App\Domains\Beneficiaries\Models\Beneficiary;

class CreateBeneficiaryAction
{
    public function execute(BeneficiaryData $data): Beneficiary
    {
        return Beneficiary::create($data->toArray());
    }
}