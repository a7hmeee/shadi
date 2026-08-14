<?php

namespace App\Domains\Beneficiaries\Actions;

use App\Domains\Beneficiaries\Models\Beneficiary;

class DeleteBeneficiaryAction
{
    public function execute(Beneficiary $beneficiary): void
    {
        $beneficiary->delete();
    }
}