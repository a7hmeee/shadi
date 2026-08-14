<?php

namespace Tests\Feature;

use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_admin_user_has_super_admin_role(): void
    {
        $user = User::where('email', 'admin@aid.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(UserRole::SUPER_ADMIN->value));
    }

    public function test_super_admin_has_all_permissions(): void
    {
        $user = User::where('email', 'admin@aid.test')->first();

        foreach (PermissionName::cases() as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission->value));
        }
    }

    public function test_admin_has_required_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);

        $this->assertTrue($admin->hasPermissionTo(PermissionName::DASHBOARD_VIEW->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::BENEFICIARIES_VIEW->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::BENEFICIARIES_CREATE->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::BENEFICIARIES_UPDATE->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::BENEFICIARIES_DELETE->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::AID_REQUESTS_VIEW->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::AID_REQUESTS_REVIEW->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::AID_REQUESTS_APPROVE->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::AID_REQUESTS_REJECT->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::AID_REQUESTS_MARK_DELIVERED->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::REPORTS_VIEW->value));
        $this->assertTrue($admin->hasPermissionTo(PermissionName::EMPLOYEES_MANAGE->value));
    }

    public function test_employee_does_not_have_approve_reject_employees_manage_permissions(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);

        $this->assertFalse($employee->hasPermissionTo(PermissionName::AID_REQUESTS_APPROVE->value));
        $this->assertFalse($employee->hasPermissionTo(PermissionName::AID_REQUESTS_REJECT->value));
        $this->assertFalse($employee->hasPermissionTo(PermissionName::EMPLOYEES_MANAGE->value));
    }

    public function test_beneficiary_cannot_access_filament_admin_panel(): void
    {
        $beneficiary = User::factory()->create();
        $beneficiary->assignRole(UserRole::BENEFICIARY->value);

        $panel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertFalse($beneficiary->canAccessPanel($panel));
    }

    public function test_super_admin_can_access_filament_admin_panel(): void
    {
        $superAdmin = User::where('email', 'admin@aid.test')->first();

        $panel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertTrue($superAdmin->canAccessPanel($panel));
    }

    public function test_user_without_role_cannot_access_filament_admin_panel(): void
    {
        $user = User::factory()->create();

        $panel = \Filament\Facades\Filament::getPanel('admin');

        $this->assertFalse($user->canAccessPanel($panel));
    }
}