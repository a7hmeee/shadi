<?php

namespace Tests\Feature;

use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Beneficiaries\Rules\BeneficiaryValidationRules;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BeneficiaryValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function validData(array $overrides = []): array
    {
        $user = User::factory()->create();
        return array_merge([
            'user_id' => $user->id,
            'national_id' => '123456789012',
            'phone' => '0501234567',
            'family_members_count' => 3,
            'children_count' => 1,
            'social_status' => SocialStatus::SINGLE->value,
            'need_level' => NeedLevel::LOW->value,
        ], $overrides);
    }

    public function test_national_id_is_required(): void
    {
        $data = $this->validData(['national_id' => '']);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('national_id', $validator->errors()->toArray());
    }

    public function test_national_id_must_be_unique(): void
    {
        $existing = Beneficiary::factory()->create();
        $data = $this->validData(['national_id' => $existing->national_id]);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
    }

    public function test_national_id_unique_ignores_current_record_on_update(): void
    {
        $beneficiary = Beneficiary::factory()->create();
        $data = $this->validData([
            'national_id' => $beneficiary->national_id,
            'user_id' => $beneficiary->user_id,
        ]);
        $validator = Validator::make($data, BeneficiaryValidationRules::forUpdate($beneficiary->id));
        $this->assertFalse($validator->fails());
    }

    public function test_family_members_count_cannot_be_less_than_one(): void
    {
        $data = $this->validData(['family_members_count' => 0]);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
    }

    public function test_children_count_cannot_be_negative(): void
    {
        $data = $this->validData(['children_count' => -1]);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
    }

    public function test_children_count_cannot_exceed_family_members(): void
    {
        $data = $this->validData(['family_members_count' => 3, 'children_count' => 5]);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        BeneficiaryValidationRules::withCrossFieldValidation($validator);
        $this->assertTrue($validator->fails());
    }

    public function test_monthly_income_cannot_be_negative(): void
    {
        $data = $this->validData(['monthly_income' => -100]);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
    }

    public function test_social_status_must_be_valid_enum(): void
    {
        $data = $this->validData(['social_status' => 'invalid_value']);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
    }

    public function test_need_level_must_be_valid_enum(): void
    {
        $data = $this->validData(['need_level' => 'invalid_value']);
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertTrue($validator->fails());
    }

    public function test_valid_data_passes(): void
    {
        $data = $this->validData();
        $validator = Validator::make($data, BeneficiaryValidationRules::forCreate());
        $this->assertFalse($validator->fails());
    }
}
