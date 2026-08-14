<?php

namespace Tests\Feature;

use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Beneficiaries\Policies\BeneficiaryPolicy;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function makeBeneficiary(): Beneficiary
    {
        return Beneficiary::factory()->create();
    }

    public function test_super_admin_can_perform_all_operations(): void
    {
        $superAdmin = User::where('email', 'admin@aid.test')->first();
        $beneficiary = $this->makeBeneficiary();

        $this->assertTrue($superAdmin->can('viewAny', Beneficiary::class));
        $this->assertTrue($superAdmin->can('view', $beneficiary));
        $this->assertTrue($superAdmin->can('create', Beneficiary::class));
        $this->assertTrue($superAdmin->can('update', $beneficiary));
        $this->assertTrue($superAdmin->can('delete', $beneficiary));
        $this->assertTrue($superAdmin->can('restore', $beneficiary));
    }

    public function test_admin_can_perform_all_operations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);
        $beneficiary = $this->makeBeneficiary();

        $this->assertTrue($admin->can('viewAny', Beneficiary::class));
        $this->assertTrue($admin->can('view', $beneficiary));
        $this->assertTrue($admin->can('create', Beneficiary::class));
        $this->assertTrue($admin->can('update', $beneficiary));
        $this->assertTrue($admin->can('delete', $beneficiary));
    }

    public function test_employee_can_view_create_update_but_not_delete(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);
        $beneficiary = $this->makeBeneficiary();

        $this->assertTrue($employee->can('viewAny', Beneficiary::class));
        $this->assertTrue($employee->can('view', $beneficiary));
        $this->assertTrue($employee->can('create', Beneficiary::class));
        $this->assertTrue($employee->can('update', $beneficiary));
        $this->assertFalse($employee->can('delete', $beneficiary));
        $this->assertFalse($employee->can('restore', $beneficiary));
    }

    public function test_beneficiary_role_cannot_perform_operations(): void
    {
        $beneficiaryUser = User::factory()->create();
        $beneficiaryUser->assignRole(UserRole::BENEFICIARY->value);
        $beneficiary = $this->makeBeneficiary();

        $this->assertFalse($beneficiaryUser->can('viewAny', Beneficiary::class));
        $this->assertFalse($beneficiaryUser->can('view', $beneficiary));
        $this->assertFalse($beneficiaryUser->can('create', Beneficiary::class));
        $this->assertFalse($beneficiaryUser->can('update', $beneficiary));
        $this->assertFalse($beneficiaryUser->can('delete', $beneficiary));
    }

    public function test_user_without_role_cannot_perform_operations(): void
    {
        $user = User::factory()->create();
        $beneficiary = $this->makeBeneficiary();

        $this->assertFalse($user->can('viewAny', Beneficiary::class));
        $this->assertFalse($user->can('view', $beneficiary));
        $this->assertFalse($user->can('create', Beneficiary::class));
        $this->assertFalse($user->can('update', $beneficiary));
        $this->assertFalse($user->can('delete', $beneficiary));
    }

    public function test_force_delete_always_false(): void
    {
        $beneficiary = $this->makeBeneficiary();
        $policy = \Illuminate\Support\Facades\Gate::getPolicyFor(Beneficiary::class);

        $superAdmin = User::where('email', 'admin@aid.test')->first();
        $admin = User::factory()->create();
        $admin->assignRole(\App\Domains\Identity\Enums\UserRole::ADMIN->value);
        $employee = User::factory()->create();
        $employee->assignRole(\App\Domains\Identity\Enums\UserRole::EMPLOYEE->value);

        $this->assertFalse($policy->forceDelete($superAdmin, $beneficiary));
        $this->assertFalse($policy->forceDelete($admin, $beneficiary));
        $this->assertFalse($policy->forceDelete($employee, $beneficiary));
    }

    public function test_policy_registered_with_laravel(): void
    {
        $policy = \Illuminate\Support\Facades\Gate::getPolicyFor(Beneficiary::class);
        $this->assertInstanceOf(BeneficiaryPolicy::class, $policy);
    }
}
