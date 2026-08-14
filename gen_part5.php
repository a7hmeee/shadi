<?php
// Generator Part 5: createRequests method - compact version
$code = <<<'PHP'
    private function createRequests(array $beneficiaries, array $staff): void
    {
        $reviewer = $staff['reviewer'];
        $approver = $staff['approver'];

        $numGen = app(\App\Domains\AidRequests\Actions\GenerateAidRequestNumberAction::class);

        // Status targets for ~85 total requests (13+17+8+12+9+20+6=85)
        $statusTargets = [
            AidRequestStatus::SUBMITTED => 13,
            AidRequestStatus::UNDER_REVIEW => 17,
            AidRequestStatus::NEEDS_INFORMATION => 8,
            AidRequestStatus::APPROVED => 12,
            AidRequestStatus::REJECTED => 9,
            AidRequestStatus::DELIVERED => 20,
            AidRequestStatus::CANCELLED => 6,
        ];

        $statusQueue = [];
        foreach ($statusTargets as $status => $count) {
            for ($j = 0; $j < $count; $j++) {
                $statusQueue[] = $status;
            }
        }
        shuffle($statusQueue);

        $aidTypeWeights = [
            AidType::FOOD->value, AidType::FOOD->value, AidType::FOOD->value, AidType::FOOD->value,
            AidType::CASH->value, AidType::CASH->value, AidType::CASH->value, AidType::CASH->value,
            AidType::MEDICAL->value, AidType::MEDICAL->value, AidType::MEDICAL->value,
            AidType::HOUSING->value, AidType::HOUSING->value, AidType::HOUSING->value,
            AidType::EDUCATION->value, AidType::EDUCATION->value, AidType::EDUCATION->value,
            AidType::EMERGENCY->value, AidType::EMERGENCY->value,
            AidType::CLOTHING->value, AidType::CLOTHING->value,
            AidType::OTHER->value,
        ];

        $aidRequests = [];
        $statusIndex = 0;
        $benCount = count($beneficiaries);

        // Ensure each beneficiary gets 1-4 requests
        $requestsPerBeneficiary = [];
        $totalTarget = array_sum($statusTargets);
        $base = (int) floor($totalTarget / $benCount);
        for ($i = 0; $i < $benCount; $i++) {
            $extra = ($i < ($totalTarget - $base * $benCount)) ? 1 : 0;
            $requestsPerBeneficiary[$i] = max(1, min(4, $base + $extra));
        }

        $idx = 0;
        foreach ($beneficiaries as $benIndex => $beneficiary) {
            $numRequests = $requestsPerBeneficiary[$benIndex] ?? 1;
            for ($r = 0; $r < $numRequests; $r++) {
                if ($statusIndex >= count($statusQueue)) {
                    break 2;
                }
                $status = $statusQueue[$statusIndex];
                $aidType = $aidTypeWeights[array_rand($aidTypeWeights)];
                $aidRequests[] = [$beneficiary, $status, $aidType, $benIndex];
                $statusIndex++;
            }
        }

        // Track dates per beneficiary
        $benDates = [];

        foreach ($aidRequests as $entry) {
            [$beneficiary, $status, $aidType, $benIndex] = $entry;
            $dayOffset = fake()->numberBetween(0, 89);
            $baseDate = Carbon::now()->subDays($dayOffset)->startOfDay();

            $submittedAt = $baseDate->copy()->addHours(fake()->numberBetween(8, 18));

            $requestData = [
                'beneficiary_id' => $beneficiary->id,
                'aid_type' => $aidType,
                'title' => $beneficiary->user->name . ' - ' . AidType::from($aidType)->label(),
                'description' => $this->requestDescriptions[$aidType][array_rand($this->requestDescriptions[$aidType])],
                'status' => $status->value,
                'submitted_at' => $submittedAt,
                'reviewed_by' => null,
                'decision_by' => null,
                'reviewed_at' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'delivered_at' => null,
                'requested_amount' => in_array($aidType, [AidType::CASH->value, AidType::EMERGENCY->value])
                    ? fake()->randomElement([200, 300, 500, 750, 1000, 1500, 2000])
                    : null,
                'approved_amount' => null,
                'rejection_reason' => null,
                'reviewer_notes' => null,
                'beneficiary_notes' => null,
                'priority_score' => null,
                'priority_level' => PriorityLevel::MEDIUM->value,
                'possible_duplicate' => false,
                'duplicate_of_request_id' => null,
            ];

            // Set timestamps based on status workflow
            if (in_array($status, [AidRequestStatus::UNDER_REVIEW, AidRequestStatus::APPROVED, AidRequestStatus::REJECTED, AidRequestStatus::DELIVERED])) {
                $reviewer = fake()->boolean(70) ? $staff['reviewer'] : $staff['approver'];
                $requestData['reviewed_by'] = $reviewer->id;
                $requestData['reviewed_at'] = $submittedAt->copy()->addHours(fake()->numberBetween(2, 48));
            }
            if (in_array($status, [AidRequestStatus::APPROVED, AidRequestStatus::DELIVERED])) {
                $decisionMaker = fake()->boolean(50) ? $staff['approver'] : $staff['admin'];
                $requestData['decision_by'] = $decisionMaker->id;
                $requestData['approved_at'] = $requestData['reviewed_at']->copy()->addHours(fake()->numberBetween(2, 24));
                $requestData['approved_amount'] = $requestData['requested_amount']
                    ?? fake()->randomElement([100, 200, 300, 500, 750, 1000, 1500, 2000]);
            }
            if ($status === AidRequestStatus::REJECTED) {
                $decisionMaker = fake()->boolean(60) ? $staff['approver'] : $staff['admin'];
                $requestData['decision_by'] = $decisionMaker->id;
                $requestData['rejected_at'] = $requestData['reviewed_at']->copy()->addHours(fake()->numberBetween(1, 12));
                $requestData['rejection_reason'] = $this->rejectionReasons[array_rand($this->rejectionReasons)];
            }
            if ($status === AidRequestStatus::NEEDS_INFORMATION) {
                $requestData['reviewer_notes'] = $this->infoNotes[array_rand($this->infoNotes)];
            }

            $requestNumber = $numGen->execute();
            $request = AidRequest::create(array_merge($requestData, ['request_number' => $requestNumber]));

            // Track for duplicate logic
            if (!isset($benDates[$benIndex])) {
                $benDates[$benIndex] = [];
            }
            $benDates[$benIndex][] = $request;
        }

        // Track demo requests for delivery
        $this->demoRequestIds = [];
        foreach ($aidRequests as $entry) {
            [$beneficiary, $status, $aidType, $benIndex] = $entry;
            if ($beneficiary->demo_flag === 'delivered' && $status === AidRequestStatus::APPROVED) {
                $this->demoRequestIds['delivered'] = $beneficiary->id;
            }
            if ($beneficiary->demo_flag === 'critical') {
                $this->demoRequestIds['critical'] = $beneficiary->id;
            }
            if ($beneficiary->demo_flag === 'duplicate') {
                $this->demoRequestIds['duplicate'] = $beneficiary->id;
            }
        }

        // Create deliberate duplicates: add a second request for duplicate-flag beneficiaries
        // with same aid_type within 30 days (to trigger duplicate detection)
        foreach ($beneficiaries as $benIndex => $beneficiary) {
            if ($beneficiary->demo_flag === 'duplicate') {
                $existing = collect($aidRequests)->filter(fn($e) => $e[3] === $benIndex)->first();
                if ($existing) {
                    $aidType = $existing[1]; // same aid type
                    $requestNumber2 = $numGen->execute();
                    $submittedAt2 = $existing[3] ?? null; // same date range
                    $submittedAt2 = Carbon::now()->subDays(fake()->numberBetween(5, 25))->startOfDay()->addHours(10);

                    AidRequest::create([
                        'beneficiary_id' => $beneficiary->id,
                        'request_number' => $requestNumber2,
                        'aid_type' => $existing[2],
                        'title' => $beneficiary->user->name . ' - ' . AidType::from($existing[2])->label(),
                        'description' => $this->requestDescriptions[$existing[2]][array_rand($this->requestDescriptions[$existing[2]])],
                        'status' => AidRequestStatus::SUBMITTED->value,
                        'submitted_at' => $submittedAt2,
                        'reviewed_by' => null,
                        'decision_by' => null,
                        'requested_amount' => null,
                        'priority_score' => null,
                        'priority_level' => PriorityLevel::MEDIUM->value,
                        'possible_duplicate' => false,
                        'duplicate_of_request_id' => null,
                    ]);
                    $this->demoRequestId = $requestNumber2;
                }
            }
        }

        // Store duplicate-request beneficiary aid type for later reference
        $this->duplicateBeneficiary = $beneficiary->demo_flag === 'duplicate' ? $beneficiary->id : null;
    }

PHP;
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code, FILE_APPEND);
echo "Part 5 written. Bytes: " . strlen($code) . "\n";
