<?php

namespace App\Domains\Eligibility\DTOs;

final readonly class DecisionRecommendationData
{
    /**
     * @param  string[]  $reasons
     * @param  string[]  $warnings
     */
    public function __construct(
        public string $recommendation,
        public string $label,
        public array $reasons,
        public array $warnings,
    ) {}
}
