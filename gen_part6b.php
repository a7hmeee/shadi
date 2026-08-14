<?php
// Base64-encoded Arabic strings
$B = [
    'doc_id' => '2LXZiNix2Kkg2YXZhiDYp9mE2YfZiNmK2Kkg2KfZhNmI2LfZhtmK2Kk=',
    'doc_inc' => '2KXYq9io2KfYqiDYr9iu2YQg2KPYs9io2YjYudmK',
    'doc_med' => '2KrZgtix2YrYsSDYt9io2Yog2K3Yr9mK2Ks=',
    'doc_hou' => '2KXYq9io2KfYqiDYs9mD2YY=',
    'doc_fam' => '2YjYq9mK2YLYqSDYudin2KbZhNmK2Kk=',
    'prefix' => '2YXYs9iq2YbYr18=',
    'note' => '2YXZhNin2K3YuNipINiq2LPZhNmK2YU=',
];

$code = '    private function createAttachments(): void
    {
        $staff = User::whereIn("email", ["admin@aid.test", "ahmad.staff@aid.test", "reem.staff@aid.test"])->get()->all();

        $docTypes = [DocumentType::IDENTITY, DocumentType::INCOME_PROOF, DocumentType::MEDICAL_REPORT, DocumentType::HOUSING_PROOF, DocumentType::FAMILY_DOCUMENT];
        $descriptions = [
            DocumentType::IDENTITY => "' . base64_decode($B['doc_id']) . '",
            DocumentType::INCOME_PROOF => "' . base64_decode($B['doc_inc']) . '",
            DocumentType::MEDICAL_REPORT => "' . base64_decode($B['doc_med']) . '",
            DocumentType::HOUSING_PROOF => "' . base64_decode($B['doc_hou']) . '",
            DocumentType::FAMILY_DOCUMENT => "' . base64_decode($B['doc_fam']) . '",
        ];

        $requests = AidRequest::inRandomOrder()->limit(15)->get();

        foreach ($requests as $request) {
            $uploader = $staff[array_rand($staff)];
            $docType = $docTypes[array_rand($docTypes)];
            $counter = $this->receiptCounter;
            $this->receiptCounter++;

            AidRequestAttachment::create([
                "aid_request_id" => $request->id,
                "uploaded_by" => $uploader->id,
                "original_name" => "' . base64_decode($B['prefix']) . '" . $counter . ".pdf",
                "file_name" => \Illuminate\Support\Str::uuid()->toString() . ".pdf",
                "file_path" => "aid-requests/" . $request->id . "/attachments/" . \Illuminate\Support\Str::uuid()->toString() . ".pdf",
                "disk" => "public",
                "mime_type" => "application/pdf",
                "file_size" => fake()->numberBetween(1024, 2000000),
                "document_type" => $docType->value,
                "description" => $descriptions[$docType],
                "is_verified" => fake()->boolean(70),
                "verified_by" => fake()->boolean(70) ? $uploader->id : null,
                "verified_at" => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(0, 30)) : null,
            ]);
        }
    }
';
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code, FILE_APPEND);
echo "Part 6b appended. Bytes: " . strlen($code) . "\n";
