<?php

namespace App\Domains\Eligibility\Services;

use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Domains\Eligibility\DTOs\PriorityResultData;
use App\Domains\Eligibility\Rules\ChronicDiseasePriorityRule;
use App\Domains\Eligibility\Rules\DisabilityPriorityRule;
use App\Domains\Eligibility\Rules\FamilySizePriorityRule;
use App\Domains\Eligibility\Rules\HousingPriorityRule;
use App\Domains\Eligibility\Rules\IncomePriorityRule;
use App\Domains\Eligibility\Rules\NeedLevelPriorityRule;
use App\Domains\Eligibility\Rules\SocialStatusPriorityRule;

class PriorityCalculatorService
{
    /**
     * @return array<string, class-string>
     */
    private function rules(): array
    {
        return [
            'income' => IncomePriorityRule::class,
            'family_size' => FamilySizePriorityRule::class,
            'disability' => DisabilityPriorityRule::class,
            'chronic_disease' => ChronicDiseasePriorityRule::class,
            'social_status' => SocialStatusPriorityRule::class,
            'housing' => HousingPriorityRule::class,
            'need_level' => NeedLevelPriorityRule::class,
        ];
    }

    public function calculate(Beneficiary $beneficiary): PriorityResultData
    {
        $breakdown = [];

        foreach ($this->rules() as $key => $ruleClass) {
            $breakdown[$key] = app($ruleClass)->score($beneficiary);
        }

        $score = min(100, max(0, array_sum($breakdown)));

        return new PriorityResultData(
            score: $score,
            level: $this->levelFor($score),
            breakdown: $breakdown,
        );
    }

    public function levelFor(int $score): PriorityLevel
    {
        return match (true) {
            $score >= 80 => PriorityLevel::CRITICAL,
            $score >= 60 => PriorityLevel::HIGH,
            $score >= 40 => PriorityLevel::MEDIUM,
            default => PriorityLevel::LOW,
        };
    }
}
