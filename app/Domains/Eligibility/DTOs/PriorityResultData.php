<?php

namespace App\Domains\Eligibility\DTOs;

use App\Domains\AidRequests\Enums\PriorityLevel;

final readonly class PriorityResultData
{
    /**
     * @param  array<string, int>  $breakdown
     */
    public function __construct(
        public int $score,
        public PriorityLevel $level,
        public array $breakdown,
    ) {}
}
