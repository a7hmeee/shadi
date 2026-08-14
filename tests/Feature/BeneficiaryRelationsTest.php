<?php

namespace Tests\Feature;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_beneficiary_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $beneficiary = Beneficiary::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $beneficiary->user);
        $this->assertEquals($user->id, $beneficiary->user->id);
    }

    public function test_user_has_one_beneficiary(): void
    {
        $user = User::factory()->create();
        $beneficiary = Beneficiary::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Beneficiary::class, $user->beneficiary);
        $this->assertEquals($beneficiary->id, $user->beneficiary->id);
    }

    public function test_beneficiary_has_many_aid_requests(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $beneficiary->aidRequests()->create([
            'request_number' => 'AID-2026-000001',
            'aid_type' => 'food',
            'title' => 'Test',
            'description' => 'Test description',
            'status' => 'submitted',
            'priority_level' => 'low',
            'requested_amount' => 100,
        ]);
        $beneficiary->aidRequests()->create([
            'request_number' => 'AID-2026-000002',
            'aid_type' => 'cash',
            'title' => 'Test 2',
            'description' => 'Test description 2',
            'status' => 'submitted',
            'priority_level' => 'low',
            'requested_amount' => 200,
        ]);

        $this->assertCount(2, $beneficiary->aidRequests);
    }

    public function test_soft_deleted_beneficiary_not_in_default_query(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $beneficiary->delete();

        $this->assertNull(Beneficiary::find($beneficiary->id));
        $this->assertNotNull(Beneficiary::withTrashed()->find($beneficiary->id));
    }
}
