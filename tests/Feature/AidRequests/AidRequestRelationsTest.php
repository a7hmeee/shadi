<?php

namespace Tests\Feature\AidRequests;

use App\Domains\AidRequests\Actions\ApproveAidRequestAction;
use App\Domains\AidRequests\Actions\RejectAidRequestAction;
use App\Domains\AidRequests\Actions\RequestMoreInformationAction;
use App\Domains\AidRequests\Actions\StartAidRequestReviewAction;
use App\Domains\AidRequests\Actions\DeleteAidRequestAction;
use App\Domains\AidRequests\DTOs\AidRequestData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\AidRequests\Models\AidDistribution;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AidRequestRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_aid_request_belongs_to_beneficiary(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $request = AidRequest::factory()->create(['beneficiary_id' => $beneficiary->id]);

        $this->assertInstanceOf(Beneficiary::class, $request->beneficiary);
        $this->assertEquals($beneficiary->id, $request->beneficiary->id);
    }

    public function test_beneficiary_has_many_aid_requests(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        AidRequest::factory()->count(3)->create(['beneficiary_id' => $beneficiary->id]);

        $this->assertCount(3, $beneficiary->aidRequests);
    }

    public function test_reviewer_relationship(): void
    {
        $reviewer = User::factory()->create();
        $request = AidRequest::factory()->create(['reviewed_by' => $reviewer->id]);

        $this->assertInstanceOf(User::class, $request->reviewer);
        $this->assertEquals($reviewer->id, $request->reviewer->id);
    }

    public function test_decision_maker_relationship(): void
    {
        $decisionMaker = User::factory()->create();
        $request = AidRequest::factory()->create(['decision_by' => $decisionMaker->id]);

        $this->assertInstanceOf(User::class, $request->decisionMaker);
        $this->assertEquals($decisionMaker->id, $request->decisionMaker->id);
    }

    public function test_attachments_relationship(): void
    {
        $request = AidRequest::factory()->create();
        AidRequestAttachment::factory()->count(2)->create(['aid_request_id' => $request->id]);

        $this->assertCount(2, $request->attachments);
    }

    public function test_distribution_relationship(): void
    {
        $request = AidRequest::factory()->create();
        $distribution = AidDistribution::factory()->create(['aid_request_id' => $request->id]);

        $this->assertInstanceOf(AidDistribution::class, $request->distribution);
        $this->assertEquals($distribution->id, $request->distribution->id);
    }

    public function test_duplicated_from_relationship(): void
    {
        $original = AidRequest::factory()->create();
        $duplicate = AidRequest::factory()->create(['duplicate_of_request_id' => $original->id]);

        $this->assertInstanceOf(AidRequest::class, $duplicate->duplicatedFrom);
        $this->assertEquals($original->id, $duplicate->duplicatedFrom->id);
    }

    public function test_soft_deleted_hidden_by_default(): void
    {
        $request = AidRequest::factory()->create();
        $request->delete();

        $this->assertNull(AidRequest::find($request->id));
        $this->assertNotNull(AidRequest::withTrashed()->find($request->id));
    }
}
