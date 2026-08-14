<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\DTOs\AidRequestData;
use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Models\AidRequest;
use Illuminate\Support\Facades\DB;

class CreateAidRequestAction
{
    public function execute(AidRequestData $data): AidRequest
    {
        return DB::transaction(function () use ($data) {
            $requestNumber = app(GenerateAidRequestNumberAction::class)->execute();

            $attributes = $data->toArray();
            $attributes['request_number'] = $requestNumber;

            if ($data->status === AidRequestStatus::SUBMITTED) {
                $attributes['submitted_at'] = now();
            }

            return AidRequest::create($attributes);
        });
    }
}