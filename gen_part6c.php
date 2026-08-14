<?php
// Base64-encoded Arabic strings for distributions
$B = [
    'label_ben' => '2KfZhNmF2LPYqtmB2YrYryA=', // المستفيد 
    'note' => '2YXZhNin2K3YuNipINiq2LPZhNmK2YU=',
    'deliv_label' => '2KrZhCDYp9mE2KrYs9mE2YrZhCDYqNmG2KzYp9it', // تم التسليم بنجاح
];

$code = '    private function createDistributions(array $staff): void
    {
        $distributor = $staff["distributor"];

        $methods = ["in_person", "transfer", "field_delivery", "quota"];
        $methodLabels = [
            "in_person" => "' . base64_decode($B['deliv_label']) . '",
            "transfer" => "' . base64_decode($B['deliv_label']) . '",
            "field_delivery" => "' . base64_decode($B['deliv_label']) . '",
            "quota" => "' . base64_decode($B['deliv_label']) . '",
        ];

        // Create distributions for all APPROVED requests that dont have one yet
        $approvedRequests = AidRequest::where("status", AidRequestStatus::APPROVED)
            ->whereDoesntHave("distribution")
            ->with("beneficiary.user")
            ->get();

        foreach ($approvedRequests as $request) {
            $method = $methods[array_rand($methods)];
            $receiptNumber = "REC-2026-" . str_pad($this->receiptCounter, 4, "0", STR_PAD_LEFT);
            $this->receiptCounter++;

            $deliveredAt = Carbon::parse($request->approved_at)->addHours(fake()->numberBetween(1, 72));

            $amount = null;
            if (in_array($request->aid_type, [AidType::CASH->value, AidType::EMERGENCY->value])) {
                $amount = $request->approved_amount ?? $request->requested_amount ?? fake()->randomElement([100, 200, 500, 1000]);
            }

            AidDistribution::create([
                "aid_request_id" => $request->id,
                "distributed_by" => $distributor->id,
                "delivered_to_name" => "' . base64_decode($B['label_ben']) . '" . $request->beneficiary->user->name,
                "delivered_to_national_id" => $request->beneficiary->national_id,
                "delivery_method" => $method,
                "amount" => $amount,
                "delivered_at" => $deliveredAt,
                "receipt_number" => $receiptNumber,
                "notes" => fake()->boolean(60) ? "' . base64_decode($B['note']) . '" : null,
            ]);

            // Update status to delivered
            $request->update([
                "status" => AidRequestStatus::DELIVERED->value,
                "delivered_at" => $deliveredAt,
            ]);
        }
    }
';
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code, FILE_APPEND);
echo "Part 6c appended. Bytes: " . strlen($code) . "\n";
