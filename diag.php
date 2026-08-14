<?php
// Quick diagnostic script
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Eligibility\Services\DuplicateDetectionService;

$service = app(DuplicateDetectionService::class);

$req = AidRequest::with('beneficiary')->first();
if ($req) {
    $result = $service->detect($req);
    echo "Score: " . $result->score . "\n";
    echo "IsDuplicate: " . ($result->isDuplicate ? "yes" : "no") . "\n";
    echo "MatchedId: " . ($result->matchedRequestId ?? "none") . "\n";
    echo "Reasons: " . implode(", ", $result->reasons) . "\n";
    echo "Total requests: " . AidRequest::count() . "\n";
    echo "Duplicate count: " . AidRequest::where('possible_duplicate', true)->count() . "\n";
}
