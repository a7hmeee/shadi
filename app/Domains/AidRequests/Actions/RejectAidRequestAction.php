<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use DomainException;

class RejectAidRequestAction
{
    public function execute(
        AidRequest $aidRequest,
        User $user,
        string $rejectionReason,
        ?string $reviewerNotes = null
    ): AidRequest {
        if ($rejectionReason === '' || $rejectionReason === null) {
            throw new DomainException('سبب الرفض مطلوب.');
        }

        $allowed = [
            AidRequestStatus::SUBMITTED,
            AidRequestStatus::UNDER_REVIEW,
            AidRequestStatus::NEEDS_INFORMATION,
        ];

        if (! in_array($aidRequest->status, $allowed, true)) {
            throw new DomainException(
                'لا يمكن رفض طلب بحالة: ' . $aidRequest->status->label()
            );
        }

        $aidRequest->update([
            'status' => AidRequestStatus::REJECTED,
            'decision_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $rejectionReason,
            'reviewer_notes' => $reviewerNotes ?? $aidRequest->reviewer_notes,
        ]);

        return $aidRequest->fresh();
    }
}