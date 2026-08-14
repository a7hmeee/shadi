<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Actions\ApproveAidRequestAction;
use App\Domains\AidRequests\Actions\CreateAidRequestAction;
use App\Domains\AidRequests\Actions\DeleteAidRequestAction;
use App\Domains\AidRequests\Actions\RequestMoreInformationAction;
use App\Domains\AidRequests\Actions\RejectAidRequestAction;
use App\Domains\AidRequests\Actions\StartAidRequestReviewAction;
use App\Domains\AidRequests\Actions\UpdateAidRequestAction;
use App\Domains\AidRequests\DTOs\AidRequestData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AidRequestActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function makeData(array $overrides = []): array
    {
        $beneficiary = \App\Domains\Beneficiaries\Models\Beneficiary::factory()->create();

        return array_merge([
            'beneficiary_id' => $beneficiary->id,
            'aid_type' => AidType::FOOD->value,
            'title' => 'طلب مساعدة غذائية',
            'description' => 'أسرة محتاجة',
            'status' => AidRequestStatus::SUBMITTED->value,
            'priority_level' => PriorityLevel::MEDIUM->value,
            'requested_amount' => 1000.00,
            'approved_amount' => null,
            'beneficiary_notes' => null,
            'reviewer_notes' => null,
        ], $overrides);
    }

    public function test_create_aid_request_creates_record(): void
    {
        $data = AidRequestData::fromArray($this->makeData());

        $request = app(CreateAidRequestAction::class)->execute($data);

        $this->assertInstanceOf(AidRequest::class, $request);
        $this->assertDatabaseHas('aid_requests', ['id' => $request->id]);
    }

    public function test_create_links_to_beneficiary(): void
    {
        $beneficiary = \App\Domains\Beneficiaries\Models\Beneficiary::factory()->create();
        $data = AidRequestData::fromArray($this->makeData(['beneficiary_id' => $beneficiary->id]));

        $request = app(CreateAidRequestAction::class)->execute($data);

        $this->assertEquals($beneficiary->id, $request->beneficiary_id);
    }

    public function test_request_number_is_generated_and_unique(): void
    {
        $r1 = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));
        $r2 = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));

        $this->assertNotNull($r1->request_number);
        $this->assertNotNull($r2->request_number);
        $this->assertNotEquals($r1->request_number, $r2->request_number);
        $this->assertMatchesRegularExpression('/^AID-\d{4}-\d{6}$/', $r1->request_number);
    }

    public function test_submitted_at_set_for_submitted_status(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));

        $this->assertNotNull($request->submitted_at);
    }

    public function test_aid_type_and_status_casts_correct(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'aid_type' => AidType::CASH->value,
        ])));

        $this->assertInstanceOf(AidType::class, $request->aid_type);
        $this->assertEquals(AidType::CASH, $request->aid_type);
        $this->assertInstanceOf(AidRequestStatus::class, $request->status);
        $this->assertEquals(AidRequestStatus::SUBMITTED, $request->status);
    }

    public function test_priority_level_casts_correct(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));

        $this->assertInstanceOf(PriorityLevel::class, $request->priority_level);
    }

    public function test_update_changes_title_and_description(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));
        $data = AidRequestData::fromArray($this->makeData([
            'title' => 'New Title',
            'description' => 'New Description',
            'requested_amount' => 2000.00,
        ]));

        $updated = app(UpdateAidRequestAction::class)->execute($request, $data);

        $this->assertEquals('New Title', $updated->title);
        $this->assertEquals('New Description', $updated->description);
        $this->assertEquals(2000.00, $updated->requested_amount);
    }

    public function test_update_does_not_create_new_record(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));
        $beforeCount = AidRequest::count();

        $data = AidRequestData::fromArray($this->makeData(['description' => 'Updated Description']));
        app(UpdateAidRequestAction::class)->execute($request, $data);

        $this->assertEquals($beforeCount, AidRequest::count());
    }

    public function test_update_preserves_request_number_and_beneficiary(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));
        $originalNumber = $request->request_number;
        $originalBeneficiary = $request->beneficiary_id;

        $data = AidRequestData::fromArray($this->makeData(['description' => 'Updated']));
        $updated = app(UpdateAidRequestAction::class)->execute($request, $data);

        $this->assertEquals($originalNumber, $updated->request_number);
        $this->assertEquals($originalBeneficiary, $updated->beneficiary_id);
    }

    public function test_start_review_submitted_to_under_review(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));

        $result = app(StartAidRequestReviewAction::class)->execute($request, $user);

        $this->assertEquals(AidRequestStatus::UNDER_REVIEW, $result->status);
        $this->assertEquals($user->id, $result->reviewed_by);
        $this->assertNotNull($result->reviewed_at);
    }

    public function test_start_review_from_delivered_fails(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::DELIVERED->value,
        ])));

        $this->expectException(\DomainException::class);
        app(StartAidRequestReviewAction::class)->execute($request, $user);
    }

    public function test_request_info_under_review_to_needs_information(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::UNDER_REVIEW->value,
        ])));

        $result = app(RequestMoreInformationAction::class)->execute($request, $user, 'Additional information needed');

        $this->assertEquals(AidRequestStatus::NEEDS_INFORMATION, $result->status);
        $this->assertEquals('Additional information needed', $result->reviewer_notes);
    }

    public function test_request_info_from_approved_fails(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::APPROVED->value,
        ])));

        $this->expectException(\DomainException::class);
        app(RequestMoreInformationAction::class)->execute($request, $user, 'Reason');
    }

    public function test_approve_under_review_to_approved(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::UNDER_REVIEW->value,
            'requested_amount' => 1500.00,
        ])));

        $result = app(ApproveAidRequestAction::class)->execute($request, $user, 1200.00, 'Partially approved');

        $this->assertEquals(AidRequestStatus::APPROVED, $result->status);
        $this->assertEquals($user->id, $result->decision_by);
        $this->assertNotNull($result->approved_at);
        $this->assertEquals(1200.00, $result->approved_amount);
    }

    public function test_approve_rejected_fails(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::REJECTED->value,
        ])));

        $this->expectException(\DomainException::class);
        app(ApproveAidRequestAction::class)->execute($request, $user, 100.00);
    }

    public function test_approve_delivered_fails(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::DELIVERED->value,
        ])));

        $this->expectException(\DomainException::class);
        app(ApproveAidRequestAction::class)->execute($request, $user, 100.00);
    }

    public function test_reject_under_review_to_rejected(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::UNDER_REVIEW->value,
        ])));

        $result = app(RejectAidRequestAction::class)->execute($request, $user, 'Does not meet criteria');

        $this->assertEquals(AidRequestStatus::REJECTED, $result->status);
        $this->assertEquals($user->id, $result->decision_by);
        $this->assertNotNull($result->rejected_at);
        $this->assertEquals('Does not meet criteria', $result->rejection_reason);
    }

    public function test_reject_requires_reason(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::UNDER_REVIEW->value,
        ])));

        $this->expectException(\DomainException::class);
        app(RejectAidRequestAction::class)->execute($request, $user, '');
    }

    public function test_reject_approved_fails(): void
    {
        $user = User::factory()->create();
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData([
            'status' => AidRequestStatus::APPROVED->value,
        ])));

        $this->expectException(\DomainException::class);
        app(RejectAidRequestAction::class)->execute($request, $user, 'Reason');
    }

    public function test_delete_soft_deletes(): void
    {
        $request = app(CreateAidRequestAction::class)->execute(AidRequestData::fromArray($this->makeData()));

        app(DeleteAidRequestAction::class)->execute($request);

        $this->assertSoftDeleted('aid_requests', ['id' => $request->id]);
        $this->assertNotNull(AidRequest::withTrashed()->find($request->id));
    }
}
