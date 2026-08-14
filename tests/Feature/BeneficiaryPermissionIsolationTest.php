<?php

namespace Tests\Feature;

use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class BeneficiaryPermissionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_employee_without_delete_permission_cannot_delete(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);
        $employee->givePermissionTo([
            PermissionName::BENEFICIARIES_VIEW->value,
            PermissionName::BENEFICIARIES_CREATE->value,
            PermissionName::BENEFICIARIES_UPDATE->value,
        ]);
        // NOT giving BENEFICIARIES_DELETE

        $beneficiary = Beneficiary::factory()->create();

        // Can view
        $this->assertTrue($employee->can('view', $beneficiary));
        // Can update
        $this->assertTrue($employee->can('update', $beneficiary));
        // Cannot delete
        $this->assertFalse($employee->can('delete', $beneficiary));

        // Even calling the policy directly must deny
        $this->assertFalse(Gate::forUser($employee)->inspect('delete', $beneficiary)->allowed());
    }

    public function test_employee_cannot_delete_even_via_direct_policy_call(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);
        $employee->givePermissionTo([
            PermissionName::BENEFICIARIES_VIEW->value,
            PermissionName::BENEFICIARIES_CREATE->value,
            PermissionName::BENEFICIARIES_UPDATE->value,
        ]);

        $beneficiary = Beneficiary::factory()->create();
        $policy = Gate::getPolicyFor(Beneficiary::class);

        $this->assertFalse($policy->delete($employee, $beneficiary));
    }
}
