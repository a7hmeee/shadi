<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\DTOs\AidRequestData;
use App\Domains\AidRequests\Models\AidRequest;
use Illuminate\Support\Facades\DB;

class UpdateAidRequestAction
{
    public function execute(AidRequest $aidRequest, AidRequestData $data): AidRequest
    {
        return DB::transaction(function () use ($aidRequest, $data) {
            // Only editable base fields. Decision fields and audit timestamps are preserved.
            $aidRequest->update([
                'beneficiary_id' => $aidRequest->beneficiary_id,
                'aid_type' => $data->aidType->value,
                'title' => $data->title,
                'description' => $data->description,
                'priority_level' => $data->priorityLevel->value,
                'requested_amount' => $data->requestedAmount,
                'beneficiary_notes' => $data->beneficiaryNotes,
            ]);

            return $aidRequest->fresh();
        });
    }
}