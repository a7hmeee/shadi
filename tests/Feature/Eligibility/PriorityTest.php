<?php

namespace Tests\Feature\Eligibility;

use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Eligibility\Actions\CalculateAidRequestPriorityAction;
use App\Domains\Eligibility\Services\PriorityCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_high_priority_beneficiary_scores_critical(): void
    {
        $beneficiary = Beneficiary::factory()->create([
            'monthly_income' => 300,
            'family_members_count' => 9,
            'has_disability' => true,
            'has_chronic_disease' => false,
            'social_status' => SocialStatus::WIDOWED->value,
            'housing_status' => HousingStatus::UNSAFE->value,
            'need_level' => NeedLevel::CRITICAL->value,
        ]);

        $result = app(PriorityCalculatorService::class)->calculate($beneficiary);

        $this->assertGreaterThanOrEqual(80, $result->score);
        $this->assertEquals(PriorityLevel::CRITICAL, $result->level);
        $this->assertArrayHasKey('income', $result->breakdown);
        $this->assertArrayHasKey('family_size', $result->breakdown);
        $this->assertArrayHasKey('housing', $result->breakdown);
    }

    public function test_low_priority_beneficiary_scores_low(): void
    {
        $beneficiary = Beneficiary::factory()->create([
            'monthly_income' => 5000,
            'family_members_count' => 2,
            'has_disability' => false,
            'has_chronic_disease' => false,
            'social_status' => SocialStatus::SINGLE->value,
            'housing_status' => HousingStatus::OWNED->value,
            'need_level' => NeedLevel::LOW->value,
        ]);

        $result = app(PriorityCalculatorService::class)->calculate($beneficiary);

        $this->assertEquals(PriorityLevel::LOW, $result->level);
        $this->assertLessThan(40, $result->score);
    }

    public function test_calculate_action_updates_request(): void
    {
        $beneficiary = Beneficiary::factory()->create([
            'monthly_income' => 300,
            'family_members_count' => 9,
            'has_disability' => true,
            'social_status' => SocialStatus::WIDOWED->value,
            'housing_status' => HousingStatus::HOMELESS->value,
            'need_level' => NeedLevel::CRITICAL->value,
        ]);

        $request = AidRequest::factory()->create(['beneficiary_id' => $beneficiary->id]);

        app(CalculateAidRequestPriorityAction::class)->execute($request);

        $request->refresh();

        $this->assertNotNull($request->priority_score);
        $this->assertNotNull($request->priority_level);
        $this->assertGreaterThanOrEqual(0, $request->priority_score);
        $this->assertLessThanOrEqual(100, $request->priority_score);
    }

    public function test_score_is_capped_at_100(): void
    {
        $beneficiary = Beneficiary::factory()->create([
            'monthly_income' => 100,
            'family_members_count' => 10,
            'has_disability' => true,
            'has_chronic_disease' => true,
            'social_status' => SocialStatus::WIDOWED->value,
            'housing_status' => HousingStatus::HOMELESS->value,
            'need_level' => NeedLevel::CRITICAL->value,
        ]);

        $result = app(PriorityCalculatorService::class)->calculate($beneficiary);

        $this->assertLessThanOrEqual(100, $result->score);
        $this->assertEquals(PriorityLevel::CRITICAL, $result->level);
    }
}
