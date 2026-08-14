<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use DomainException;

class RequestMoreInformationAction
{
    public function execute(AidRequest $aidRequest, User $user, ?string $reviewerNotes = null): AidRequest
    {
        $allowed = [
            AidRequestStatus::SUBMITTED,
            AidRequestStatus::UNDER_REVIEW,
        ];

        if (! in_array($aidRequest->status, $allowed, true)) {
            throw new DomainException(
                'لا يمكن طلب معلومات إضافية لطلب بحالة: ' . $aidRequest->status->label()
            );
        }

        $aidRequest->update([
            'status' => AidRequestStatus::NEEDS_INFORMATION,
            'reviewed_by' => $user->id,
            'reviewed_at' => $user->id === $aidRequest->reviewed_by ? $aidRequest->reviewed_at : now(),
            'reviewer_notes' => $reviewerNotes,
        ]);

        return $aidRequest->fresh();
    }
}