<?php

namespace App\Domains\Eligibility\Actions;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Eligibility\DTOs\PriorityResultData;
use App\Domains\Eligibility\Services\PriorityCalculatorService;
use DomainException;

class CalculateAidRequestPriorityAction
{
    public function __construct(
        private readonly PriorityCalculatorService $calculator,
    ) {}

    public function execute(AidRequest $aidRequest): PriorityResultData
    {
        $beneficiary = $aidRequest->beneficiary()->first();

        if ($beneficiary === null) {
            throw new DomainException('لا يمكن حساب الأولوية بدون مستفيد مرتبط بالطلب.');
        }

        $result = $this->calculator->calculate($beneficiary);

        $aidRequest->update([
            'priority_score' => $result->score,
            'priority_level' => $result->level->value,
        ]);

        return $result;
    }
}
