<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Policies\AidRequestPolicy;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AidRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function makeRequest(): AidRequest
    {
        return AidRequest::factory()->create();
    }

    public function test_super_admin_can_all_operations(): void
    {
        $user = User::where('email', 'admin@aid.test')->first();
        $request = $this->makeRequest();

        $this->assertTrue($user->can('viewAny', AidRequest::class));
        $this->assertTrue($user->can('view', $request));
        $this->assertTrue($user->can('create', AidRequest::class));
        $this->assertTrue($user->can('review', $request));
        $this->assertTrue($user->can('approve', $request));
        $this->assertTrue($user->can('reject', $request));
    }

    public function test_admin_has_review_approve_reject_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);
        $request = $this->makeRequest();

        $this->assertTrue($admin->can('view', $request));
        $this->assertTrue($admin->can('review', $request));
        $this->assertTrue($admin->can('approve', $request));
        $this->assertTrue($admin->can('reject', $request));
    }

    public function test_employee_can_view_and_review_but_not_approve_reject(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);
        $request = $this->makeRequest();

        $this->assertTrue($employee->can('view', $request));
        $this->assertTrue($employee->can('review', $request));
        $this->assertFalse($employee->can('approve', $request));
        $this->assertFalse($employee->can('reject', $request));
        $this->assertFalse($employee->can('markDelivered', $request));
    }

    public function test_employee_cannot_delete_request(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole(UserRole::EMPLOYEE->value);
        $request = $this->makeRequest();

        $this->assertFalse($employee->can('delete', $request));
    }

    public function test_beneficiary_cannot_perform_operations(): void
    {
        $beneficiary = User::factory()->create();
        $beneficiary->assignRole(UserRole::BENEFICIARY->value);
        $request = $this->makeRequest();

        $this->assertFalse($beneficiary->can('viewAny', AidRequest::class));
        $this->assertFalse($beneficiary->can('view', $request));
        $this->assertFalse($beneficiary->can('review', $request));
        $this->assertFalse($beneficiary->can('approve', $request));
        $this->assertFalse($beneficiary->can('reject', $request));
    }

    public function test_user_without_role_cannot_anything(): void
    {
        $user = User::factory()->create();
        $request = $this->makeRequest();

        $this->assertFalse($user->can('viewAny', AidRequest::class));
        $this->assertFalse($user->can('view', $request));
        $this->assertFalse($user->can('review', $request));
        $this->assertFalse($user->can('approve', $request));
    }

    public function test_force_delete_always_false(): void
    {
        $user = User::where('email', 'admin@aid.test')->first();
        $request = $this->makeRequest();

        $this->assertFalse($user->can('forceDelete', $request));
    }

    public function test_policy_registered(): void
    {
        $policy = \Illuminate\Support\Facades\Gate::getPolicyFor(AidRequest::class);
        $this->assertInstanceOf(AidRequestPolicy::class, $policy);
    }

    public function test_delete_disabled_in_mvp(): void
    {
        $policy = \Illuminate\Support\Facades\Gate::getPolicyFor(AidRequest::class);
        $user = User::where('email', 'admin@aid.test')->first();
        $request = $this->makeRequest();

        $this->assertFalse($policy->delete($user, $request));
    }
}
