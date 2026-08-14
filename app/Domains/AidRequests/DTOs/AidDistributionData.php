<?php

namespace App\Domains\AidRequests\DTOs;

use Carbon\Carbon;

final readonly class AidDistributionData
{
    public function __construct(
        public string $deliveredToName,
        public ?string $deliveredToNationalId,
        public ?string $deliveryMethod,
        public ?float $amount,
        public Carbon $deliveredAt,
        public ?string $receiptNumber,
        public ?string $notes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deliveredToName: (string) ($data['delivered_to_name'] ?? ''),
            deliveredToNationalId: isset($data['delivered_to_national_id']) && $data['delivered_to_national_id'] !== '' && $data['delivered_to_national_id'] !== null
                ? (string) $data['delivered_to_national_id']
                : null,
            deliveryMethod: isset($data['delivery_method']) && $data['delivery_method'] !== '' && $data['delivery_method'] !== null
                ? (string) $data['delivery_method']
                : null,
            amount: isset($data['amount']) && $data['amount'] !== '' && $data['amount'] !== null
                ? (float) $data['amount']
                : null,
            deliveredAt: Carbon::parse($data['delivered_at'] ?? now()->toDateTimeString()),
            receiptNumber: isset($data['receipt_number']) && $data['receipt_number'] !== '' && $data['receipt_number'] !== null
                ? (string) $data['receipt_number']
                : null,
            notes: isset($data['notes']) && $data['notes'] !== '' && $data['notes'] !== null
                ? (string) $data['notes']
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'delivered_to_name' => $this->deliveredToName,
            'delivered_to_national_id' => $this->deliveredToNationalId,
            'delivery_method' => $this->deliveryMethod,
            'amount' => $this->amount,
            'delivered_at' => $this->deliveredAt->toDateTimeString(),
            'receipt_number' => $this->receiptNumber,
            'notes' => $this->notes,
        ];
    }
}
