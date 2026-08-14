<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use DomainException;

class ApproveAidRequestAction
{
    public function execute(
        AidRequest $aidRequest,
        User $user,
        ?float $approvedAmount = null,
        ?string $reviewerNotes = null
    ): AidRequest {
        $allowed = [
            AidRequestStatus::SUBMITTED,
            AidRequestStatus::UNDER_REVIEW,
            AidRequestStatus::NEEDS_INFORMATION,
        ];

        if (! in_array($aidRequest->status, $allowed, true)) {
            throw new DomainException(
                'لا يمكن الموافقة على طلب بحالة: ' . $aidRequest->status->label()
            );
        }

        $aidRequest->update([
            'status' => AidRequestStatus::APPROVED,
            'decision_by' => $user->id,
            'approved_at' => now(),
            'approved_amount' => $approvedAmount ?? $aidRequest->requested_amount,
            'reviewer_notes' => $reviewerNotes ?? $aidRequest->reviewer_notes,
        ]);

        return $aidRequest->fresh();
    }
}