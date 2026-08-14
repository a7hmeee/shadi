<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Models\AidRequest;
use Illuminate\Support\Str;

class GenerateAidRequestNumberAction
{
    public function execute(): string
    {
        $year = now()->format('Y');

        do {
            $sequence = str_pad(
                random_int(1, 999999),
                6,
                '0',
                STR_PAD_LEFT,
            );

            $number = "AID-{$year}-{$sequence}";
        } while (AidRequest::where('request_number', $number)->exists());

        return $number;
    }
}