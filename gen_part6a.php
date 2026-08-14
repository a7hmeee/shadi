<?php
$code = '    private function computePriorities(): void
    {
        $calcAction = app(CalculateAidRequestPriorityAction::class);
        $requests = AidRequest::with("beneficiary")->get();
        foreach ($requests as $request) {
            if ($request->beneficiary) {
                $calcAction->execute($request);
            }
        }
    }

    private function detectDuplicates(): void
    {
        $detectAction = app(DetectAidRequestDuplicateAction::class);
        $requests = AidRequest::query()->with("beneficiary")->get();
        foreach ($requests as $request) {
            try {
                $detectAction->execute($request);
            } catch (\Throwable $e) {
                // skip on error
            }
        }
    }
';
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code, FILE_APPEND);
echo "Part 6a appended. Bytes: " . strlen($code) . "\n";
