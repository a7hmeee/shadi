<?php

namespace App\Domains\Eligibility\Actions;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Eligibility\DTOs\DuplicateDetectionResultData;
use App\Domains\Eligibility\Services\DuplicateDetectionService;

class DetectAidRequestDuplicateAction
{
    public function __construct(private readonly DuplicateDetectionService $service) {}

    public function execute(AidRequest $aidRequest): DuplicateDetectionResultData
    {
        $result = $this->service->detect($aidRequest);

        $aidRequest->forceFill([
            'possible_duplicate' => $result->isDuplicate,
            'duplicate_of_request_id' => $result->matchedRequestId,
        ])->save();

        return $result;
    }
}
