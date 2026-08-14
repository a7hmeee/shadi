<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use DomainException;

class StartAidRequestReviewAction
{
    public function execute(AidRequest $aidRequest, User $user): AidRequest
    {
        $allowed = [
            AidRequestStatus::SUBMITTED,
            AidRequestStatus::NEEDS_INFORMATION,
        ];

        if (! in_array($aidRequest->status, $allowed, true)) {
            throw new DomainException(
                'لا يمكن بدء المراجعة لطلب بحالة: ' . $aidRequest->status->label()
            );
        }

        $aidRequest->update([
            'status' => AidRequestStatus::UNDER_REVIEW,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return $aidRequest->fresh();
    }
}