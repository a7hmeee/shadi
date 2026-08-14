<?php

namespace App\Domains\Beneficiaries\Policies;

use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Models\User;

class BeneficiaryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('beneficiaries.view');
    }

    public function view(User $user, Beneficiary $beneficiary): bool
    {
        return $user->can('beneficiaries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('beneficiaries.create');
    }

    public function update(User $user, Beneficiary $beneficiary): bool
    {
        return $user->can('beneficiaries.update');
    }

    public function delete(User $user, Beneficiary $beneficiary): bool
    {
        return $user->can('beneficiaries.delete');
    }

    public function restore(User $user, Beneficiary $beneficiary): bool
    {
        return $user->can('beneficiaries.delete');
    }

    public function forceDelete(User $user, Beneficiary $beneficiary): bool
    {
        return false;
    }
}