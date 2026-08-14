<?php

namespace App\Domains\Eligibility\DTOs;

class DuplicateDetectionResultData
{
    public function __construct(
        public readonly int $score,
        public readonly bool $isDuplicate,
        public readonly ?int $matchedRequestId,
        /** @var string[] */
        public readonly array $reasons,
    ) {}

    public static function fromScore(int $score, bool $isDuplicate, ?int $matchedRequestId, array $reasons): self
    {
        return new self(min(100, max(0, $score)), $isDuplicate, $matchedRequestId, $reasons);
    }

    public function reasonsList(): string
    {
        return implode(', ', $this->reasons);
    }
}
