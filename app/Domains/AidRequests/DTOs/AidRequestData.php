<?php

namespace App\Domains\AidRequests\DTOs;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;

final readonly class AidRequestData
{
    public function __construct(
        public int $beneficiaryId,
        public AidType $aidType,
        public ?string $title,
        public string $description,
        public AidRequestStatus $status,
        public PriorityLevel $priorityLevel,
        public ?float $requestedAmount,
        public ?float $approvedAmount,
        public ?string $beneficiaryNotes,
        public ?string $reviewerNotes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            beneficiaryId: (int) ($data['beneficiary_id'] ?? 0),
            aidType: AidType::from($data['aid_type'] ?? AidType::OTHER->value),
            title: $data['title'] ?? null,
            description: (string) ($data['description'] ?? ''),
            status: AidRequestStatus::from($data['status'] ?? AidRequestStatus::SUBMITTED->value),
            priorityLevel: PriorityLevel::from($data['priority_level'] ?? PriorityLevel::MEDIUM->value),
            requestedAmount: isset($data['requested_amount']) && $data['requested_amount'] !== null && $data['requested_amount'] !== ''
                ? (float) $data['requested_amount']
                : null,
            approvedAmount: isset($data['approved_amount']) && $data['approved_amount'] !== null && $data['approved_amount'] !== ''
                ? (float) $data['approved_amount']
                : null,
            beneficiaryNotes: $data['beneficiary_notes'] ?? null,
            reviewerNotes: $data['reviewer_notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'beneficiary_id' => $this->beneficiaryId,
            'aid_type' => $this->aidType->value,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'priority_level' => $this->priorityLevel->value,
            'requested_amount' => $this->requestedAmount,
            'approved_amount' => $this->approvedAmount,
            'beneficiary_notes' => $this->beneficiaryNotes,
            'reviewer_notes' => $this->reviewerNotes,
        ];
    }
}
