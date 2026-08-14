<?php

namespace Tests\Feature;

use App\Domains\Beneficiaries\Actions\CreateBeneficiaryAction;
use App\Domains\Beneficiaries\Actions\DeleteBeneficiaryAction;
use App\Domains\Beneficiaries\Actions\UpdateBeneficiaryAction;
use App\Domains\Beneficiaries\DTOs\BeneficiaryData;
use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeneficiaryActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    public function test_create_beneficiary_action_creates_record(): void
    {
        $user = User::factory()->create();

        $data = BeneficiaryData::fromArray([
            'user_id' => $user->id,
            'national_id' => '123456789012',
            'phone' => '0501234567',
            'alternative_phone' => null,
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'social_status' => SocialStatus::MARRIED->value,
            'family_members_count' => 5,
            'children_count' => 2,
            'dependents_count' => 1,
            'monthly_income' => 2000.00,
            'has_disability' => false,
            'disability_details' => null,
            'has_chronic_disease' => false,
            'chronic_disease_details' => null,
            'housing_status' => HousingStatus::OWNED->value,
            'need_level' => NeedLevel::MEDIUM->value,
            'governorate' => 'Riyadh',
            'city' => 'Riyadh',
            'address' => 'Some address',
            'notes' => null,
            'is_active' => true,
        ]);

        $beneficiary = app(CreateBeneficiaryAction::class)->execute($data);

        $this->assertInstanceOf(Beneficiary::class, $beneficiary);
        $this->assertDatabaseHas('beneficiaries', [
            'id' => $beneficiary->id,
            'national_id' => '123456789012',
            'user_id' => $user->id,
        ]);
        $this->assertEquals(SocialStatus::MARRIED, $beneficiary->social_status);
        $this->assertEquals(NeedLevel::MEDIUM, $beneficiary->need_level);
        $this->assertEquals(HousingStatus::OWNED, $beneficiary->housing_status);
    }

    public function test_create_beneficiary_action_stores_enums_correctly(): void
    {
        $user = User::factory()->create();

        $data = BeneficiaryData::fromArray([
            'user_id' => $user->id,
            'national_id' => '987654321098',
            'phone' => '0509876543',
            'alternative_phone' => null,
            'date_of_birth' => null,
            'gender' => 'female',
            'social_status' => SocialStatus::WIDOWED->value,
            'family_members_count' => 3,
            'children_count' => 1,
            'dependents_count' => 0,
            'monthly_income' => 1500.00,
            'has_disability' => true,
            'disability_details' => 'Visual impairment',
            'has_chronic_disease' => true,
            'chronic_disease_details' => 'Diabetes',
            'housing_status' => HousingStatus::RENTED->value,
            'need_level' => NeedLevel::HIGH->value,
            'governorate' => 'Makkah',
            'city' => 'Jeddah',
            'address' => 'Test',
            'notes' => null,
            'is_active' => true,
        ]);

        $beneficiary = app(CreateBeneficiaryAction::class)->execute($data);

        $this->assertEquals(SocialStatus::WIDOWED, $beneficiary->social_status);
        $this->assertEquals(NeedLevel::HIGH, $beneficiary->need_level);
        $this->assertEquals(HousingStatus::RENTED, $beneficiary->housing_status);
        $this->assertTrue($beneficiary->has_disability);
        $this->assertEquals('Visual impairment', $beneficiary->disability_details);
    }

    public function test_create_beneficiary_links_to_correct_user(): void
    {
        $user = User::factory()->create();
        $wrongUser = User::factory()->create();

        $data = BeneficiaryData::fromArray([
            'user_id' => $user->id,
            'national_id' => '111222333444',
            'phone' => '0501112223',
            'alternative_phone' => null,
            'date_of_birth' => null,
            'gender' => 'male',
            'social_status' => SocialStatus::SINGLE->value,
            'family_members_count' => 1,
            'children_count' => 0,
            'dependents_count' => 0,
            'monthly_income' => 0,
            'has_disability' => false,
            'disability_details' => null,
            'has_chronic_disease' => false,
            'chronic_disease_details' => null,
            'housing_status' => null,
            'need_level' => NeedLevel::LOW->value,
            'governorate' => null,
            'city' => null,
            'address' => null,
            'notes' => null,
            'is_active' => true,
        ]);

        $beneficiary = app(CreateBeneficiaryAction::class)->execute($data);

        $this->assertEquals($user->id, $beneficiary->user_id);
        $this->assertNotEquals($wrongUser->id, $beneficiary->user_id);
    }

    public function test_update_beneficiary_action_updates_data(): void
    {
        $beneficiary = Beneficiary::factory()->create(['phone' => '0500000000', 'need_level' => NeedLevel::LOW->value]);

        $data = BeneficiaryData::fromArray([
            'user_id' => $beneficiary->user_id,
            'national_id' => $beneficiary->national_id,
            'phone' => '0599999999',
            'alternative_phone' => null,
            'date_of_birth' => null,
            'gender' => 'male',
            'social_status' => SocialStatus::SINGLE->value,
            'family_members_count' => 2,
            'children_count' => 0,
            'dependents_count' => 0,
            'monthly_income' => 1000.00,
            'has_disability' => false,
            'disability_details' => null,
            'has_chronic_disease' => false,
            'chronic_disease_details' => null,
            'housing_status' => null,
            'need_level' => NeedLevel::CRITICAL->value,
            'governorate' => null,
            'city' => null,
            'address' => null,
            'notes' => null,
            'is_active' => true,
        ]);

        $updated = app(UpdateBeneficiaryAction::class)->execute($beneficiary, $data);

        $this->assertEquals('0599999999', $updated->phone);
        $this->assertEquals(NeedLevel::CRITICAL, $updated->need_level);
        $this->assertEquals($beneficiary->id, $updated->id);
    }

    public function test_update_beneficiary_action_does_not_create_new_record(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $beforeCount = Beneficiary::count();

        $data = BeneficiaryData::fromArray([
            'user_id' => $beneficiary->user_id,
            'national_id' => $beneficiary->national_id,
            'phone' => '0588888888',
            'alternative_phone' => null,
            'date_of_birth' => null,
            'gender' => 'male',
            'social_status' => SocialStatus::MARRIED->value,
            'family_members_count' => 3,
            'children_count' => 1,
            'dependents_count' => 0,
            'monthly_income' => 500.00,
            'has_disability' => false,
            'disability_details' => null,
            'has_chronic_disease' => false,
            'chronic_disease_details' => null,
            'housing_status' => null,
            'need_level' => NeedLevel::MEDIUM->value,
            'governorate' => null,
            'city' => null,
            'address' => null,
            'notes' => null,
            'is_active' => true,
        ]);

        app(UpdateBeneficiaryAction::class)->execute($beneficiary, $data);

        $this->assertEquals($beforeCount, Beneficiary::count());
    }

    public function test_delete_beneficiary_action_soft_deletes(): void
    {
        $beneficiary = Beneficiary::factory()->create();

        app(DeleteBeneficiaryAction::class)->execute($beneficiary);

        $this->assertSoftDeleted('beneficiaries', ['id' => $beneficiary->id]);
        $this->assertNull(Beneficiary::find($beneficiary->id));
        $this->assertNotNull(Beneficiary::withTrashed()->find($beneficiary->id));
    }
}
