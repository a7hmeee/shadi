<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Models\AidRequest;

class DeleteAidRequestAction
{
    public function execute(AidRequest $aidRequest): void
    {
        $aidRequest->delete();
    }
}