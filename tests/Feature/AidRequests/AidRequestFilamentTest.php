<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Actions\ApproveAidRequestAction;
use App\Domains\AidRequests\Actions\RejectAidRequestAction;
use App\Domains\AidRequests\Actions\StartAidRequestReviewAction;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Filament\Resources\AidRequests\Pages\CreateAidRequest;
use App\Filament\Resources\AidRequests\Pages\EditAidRequest;

use App\Filament\Resources\AidRequests\Pages\ListAidRequests;
use App\Filament\Resources\AidRequests\Pages\ViewAidRequest;
use App\Domains\Identity\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AidRequestFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function superAdmin(): User
    {
        return User::where('email', 'admin@aid.test')->first();
    }

    private function employee(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::EMPLOYEE->value);
        return $user;
    }

    private function beneficiaryUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRole::BENEFICIARY->value);
        return $user;
    }

    private function makeRequest(array $overrides = []): AidRequest
    {
        return AidRequest::factory()->create($overrides);
    }

    // LIST
    public function test_super_admin_can_open_list_page(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(ListAidRequests::class)
            ->assertSuccessful();
    }

    public function test_employee_can_open_list_page(): void
    {
        Livewire::actingAs($this->employee())
            ->test(ListAidRequests::class)
            ->assertSuccessful();
    }

    public function test_beneficiary_cannot_open_list_page(): void
    {
        Livewire::actingAs($this->beneficiaryUser())
            ->test(ListAidRequests::class)
            ->assertForbidden();
    }

    public function test_list_page_shows_existing_requests(): void
    {
        AidRequest::factory()->count(3)->create();

        Livewire::actingAs($this->superAdmin())
            ->test(ListAidRequests::class)
            ->assertCanSeeTableRecords(AidRequest::all());
    }

    public function test_list_page_search_by_request_number(): void
    {
        $request = AidRequest::factory()->create(['request_number' => 'AID-2026-000001']);

        Livewire::actingAs($this->superAdmin())
            ->test(ListAidRequests::class)
            ->searchTable('AID-2026-000001')
            ->assertCanSeeTableRecords([$request]);
    }

    public function test_list_page_search_by_beneficiary_name(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $user = $beneficiary->user;
        $request = AidRequest::factory()->create(['beneficiary_id' => $beneficiary->id]);

        Livewire::actingAs($this->superAdmin())
            ->test(ListAidRequests::class)
            ->searchTable($user->name)
            ->assertCanSeeTableRecords([$request]);
    }

    public function test_list_page_status_filter(): void
    {
        $approved = AidRequest::factory()->create(['status' => AidRequestStatus::APPROVED->value]);
        $submitted = AidRequest::factory()->create(['status' => AidRequestStatus::SUBMITTED->value]);

        Livewire::actingAs($this->superAdmin())
            ->test(ListAidRequests::class)
            ->filterTable('status', AidRequestStatus::APPROVED->value)
            ->assertCanSeeTableRecords([$approved])
            ->assertCanNotSeeTableRecords([$submitted]);
    }

    public function test_list_page_aid_type_filter(): void
    {
        $food = AidRequest::factory()->create(['aid_type' => AidType::FOOD->value]);
        $cash = AidRequest::factory()->create(['aid_type' => AidType::CASH->value]);

        Livewire::actingAs($this->superAdmin())
            ->test(ListAidRequests::class)
            ->filterTable('aid_type', AidType::FOOD->value)
            ->assertCanSeeTableRecords([$food])
            ->assertCanNotSeeTableRecords([$cash]);
    }

    // CREATE
    public function test_super_admin_can_open_create_page(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(CreateAidRequest::class)
            ->assertSuccessful();
    }

    public function test_create_page_can_create_request(): void
    {
        $beneficiary = Beneficiary::factory()->create();

        Livewire::actingAs($this->superAdmin())
            ->test(CreateAidRequest::class)
            ->fillForm([
                'beneficiary_id' => $beneficiary->id,
                'aid_type' => AidType::FOOD->value,
                'title' => 'طلب غذائي',
                'description' => 'لأسرة محتاجة',
                'requested_amount' => 1000.00,
                'beneficiary_notes' => 'حالة طوارئ',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('aid_requests', [
            'aid_type' => AidType::FOOD->value,
            'title' => 'طلب غذائي',
            'beneficiary_id' => $beneficiary->id,
        ]);
    }

    public function test_create_page_validation_errors_for_bad_data(): void
    {
        Livewire::actingAs($this->superAdmin())
            ->test(CreateAidRequest::class)
            ->fillForm([
                'beneficiary_id' => '', // required
                'aid_type' => AidType::FOOD->value,
                'title' => '',
                'description' => '',
                'requested_amount' => -10, // should fail
            ])
            ->call('create')
            ->assertHasFormErrors(['beneficiary_id', 'title', 'description', 'requested_amount']);
    }

    public function test_create_page_rejects_duplicate_request_number(): void
    {
        $existing = AidRequest::factory()->create();
        $beneficiary = Beneficiary::factory()->create();

        Livewire::actingAs($this->superAdmin())
            ->test(CreateAidRequest::class)
            ->fillForm([
                'beneficiary_id' => $beneficiary->id,
                'aid_type' => AidType::FOOD->value,
                'request_number' => $existing->request_number, // duplicate
                'title' => 'Duplicate',
                'description' => 'Desc',
                'requested_amount' => 100.00,
            ])
            ->call('create')
            ->assertHasFormErrors(['request_number']);
    }

    // EDIT
    public function test_edit_page_loads_request_data(): void
    {
        $request = AidRequest::factory()->create(['request_number' => 'REQ-001']);

        Livewire::actingAs($this->superAdmin())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->assertFormSet([
                'request_number' => 'REQ-001',
                'aid_type' => $request->aid_type->value,
                'title' => $request->title,
                'description' => $request->description,
            ]);
    }

    public function test_edit_page_can_update_request(): void
    {
        $request = AidRequest::factory()->create(['title' => 'Old Title']);

        Livewire::actingAs($this->superAdmin())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->fillForm([
                'beneficiary_id' => $request->beneficiary_id,
                'aid_type' => AidType::CASH->value,
                'request_number' => $request->request_number,
                'title' => 'New Title',
                'description' => 'New Desc',
                'requested_amount' => 2000.00,
                'status' => $request->status->value,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('aid_requests', [
            'id' => $request->id,
            'title' => 'New Title',
            'description' => 'New Desc',
        ]);
    }

    // REVIEW
    public function test_super_admin_can_start_review(): void
    {
        $request = AidRequest::factory()->create(['status' => AidRequestStatus::SUBMITTED->value]);

        Livewire::actingAs($this->superAdmin())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->callAction('startReview')
            ->assertSuccessful();

        $request->refresh();
        $this->assertEquals(AidRequestStatus::UNDER_REVIEW, $request->status);
    }

    public function test_employee_can_request_info(): void
    {
        $request = AidRequest::factory()->create(['status' => AidRequestStatus::SUBMITTED->value]);

        Livewire::actingAs($this->employee())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->fillForm(['reviewer_notes' => 'نحتاج مستندات'])
            ->callAction('requestInfo')
            ->assertSuccessful();

        $request->refresh();
        $this->assertEquals(AidRequestStatus::NEEDS_INFORMATION, $request->status);
    }

    public function test_super_admin_can_approve(): void
    {
        $request = AidRequest::factory()->create([
            'status' => AidRequestStatus::UNDER_REVIEW->value,
            'requested_amount' => 1500.00,
        ]);

        Livewire::actingAs($this->superAdmin())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->fillForm([
                'status' => AidRequestStatus::APPROVED->value,
                'approved_amount' => 1200.00,
                'reviewer_notes' => 'Approved',
            ])
            ->callAction('approve')
            ->assertSuccessful();

        $request->refresh();
        $this->assertEquals(AidRequestStatus::APPROVED, $request->status);
    }

    public function test_super_admin_can_reject(): void
    {
        $request = AidRequest::factory()->create([
            'status' => AidRequestStatus::UNDER_REVIEW->value,
        ]);

        Livewire::actingAs($this->superAdmin())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->fillForm([
                'status' => AidRequestStatus::REJECTED->value,
                'rejection_reason' => 'لا يستوفي الشروط',
                'reviewer_notes' => 'Rejected',
            ])
            ->callAction('reject')
            ->assertSuccessful();

        $request->refresh();
        $this->assertEquals(AidRequestStatus::REJECTED, $request->status);
    }

    // SECURITY
    public function test_employee_cannot_start_review_without_permission(): void
    {
        $request = AidRequest::factory()->create(['status' => AidRequestStatus::SUBMITTED->value]);

        // The edit page should load (employee can view)
        Livewire::actingAs($this->employee())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->assertSuccessful();

        // But review action should be hidden (employee doesn't have review permission)
        Livewire::actingAs($this->employee())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->assertActionHidden('startReview');
    }

    public function test_employee_cannot_approve_or_reject(): void
    {
        $request = AidRequest::factory()->create(['status' => AidRequestStatus::UNDER_REVIEW->value]);

        // Employee can view but not approve
        Livewire::actingAs($this->employee())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->assertActionHidden('approve');
        Livewire::actingAs($this->employee())
            ->test(EditAidRequest::class, ['record' => $request->id])
            ->assertActionHidden('reject');
    }

    // VIEW
    public function test_view_page_displays_request_data(): void
    {
        $request = AidRequest::factory()->create(['request_number' => 'VIEW-001']);

        Livewire::actingAs($this->superAdmin())
            ->test(ViewAidRequest::class, ['record' => $request->id])
            ->assertSuccessful();
    }
}
