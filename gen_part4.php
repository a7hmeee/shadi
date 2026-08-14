<?php
// Generator Part 4: createBeneficiaries method
$code = <<<'PHP'
    private function createBeneficiaries(int $count): array
    {
        $beneficiaries = [];
        $usedNationalIds = [];
        $usedPhones = [];

        $demoSpecs = [
            ['name' => 'سامر محمود أبو عرام', 'gender' => 'male', 'city' => 'الخليل', 'demo' => 'critical'],
            ['name' => 'ليلى أحمد الجعبري', 'gender' => 'female', 'city' => 'دورا', 'demo' => 'duplicate'],
            ['name' => 'خالد يوسف الحروب', 'gender' => 'male', 'city' => 'إذنا', 'demo' => 'delivered'],
        ];

        for ($i = 0; $i < $count; $i++) {
            if ($i < 3) {
                $parts = explode(' ', $demoSpecs[$i]['name'], 2);
                $firstName = $parts[0];
                $lastName = $parts[1];
                $gender = $demoSpecs[$i]['gender'];
                $city = $demoSpecs[$i]['city'];
                $demoFlag = $demoSpecs[$i]['demo'];
            } else {
                $gender = fake()->boolean(50) ? 'male' : 'female';
                $firstName = ($gender === 'male')
                    ? $this->maleFirstNames[array_rand($this->maleFirstNames)]
                    : $this->femaleFirstNames[array_rand($this->femaleFirstNames)];
                $lastName = $this->lastNames[array_rand($this->lastNames)];
                $city = $this->cities[array_rand($this->cities)];
                $demoFlag = null;
            }

            $fullName = $firstName . ' ' . $lastName;

            $user = User::create([
                'name' => $fullName,
                'email' => 'ben_' . $i . '_' . time() . '@aid.test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole(UserRole::BENEFICIARY->value);

            do {
                $nationalId = (string) random_int(100000000, 999999999);
            } while (in_array($nationalId, $usedNationalIds));
            $usedNationalIds[] = $nationalId;

            do {
                $prefix = ['059', '056', '052', '054'][array_rand(['059', '056', '052', '054'])];
                $phone = $prefix . random_int(1000000, 9999999);
            } while (in_array($phone, $usedPhones));
            $usedPhones[] = $phone;

            $familyMembers = match ($i % 8) {
                0 => fake()->numberBetween(5, 7),
                1 => fake()->numberBetween(8, 12),
                2 => fake()->numberBetween(3, 5),
                3 => fake()->numberBetween(4, 6),
                4 => fake()->numberBetween(2, 3),
                5 => fake()->numberBetween(7, 9),
                6 => fake()->numberBetween(4, 8),
                7 => fake()->numberBetween(3, 10),
            };
            $children = fake()->numberBetween(0, max(1, $familyMembers - 2));
            $dependents = fake()->numberBetween(0, max(0, $familyMembers - $children - 1));
            $incomeBrackets = [0, 300, 800, 1200, 2000, 3000, 4500];
            $income = $incomeBrackets[array_rand($incomeBrackets)] + fake()->numberBetween(-50, 300);
            $income = (int) max(0, min(4500, $income));

            $statusPool = [
                SocialStatus::MARRIED->value, SocialStatus::MARRIED->value,
                SocialStatus::MARRIED->value, SocialStatus::MARRIED->value,
                SocialStatus::WIDOWED->value, SocialStatus::MARRIED->value,
                SocialStatus::DIVORCED->value, SocialStatus::MARRIED->value,
                SocialStatus::SINGLE->value, SocialStatus::SEPARATED->value,
            ];
            $socialStatus = $statusPool[array_rand($statusPool)];

            $needPool = [
                NeedLevel::HIGH->value, NeedLevel::HIGH->value, NeedLevel::CRITICAL->value,
                NeedLevel::MEDIUM->value, NeedLevel::HIGH->value, NeedLevel::CRITICAL->value,
                NeedLevel::LOW->value, NeedLevel::MEDIUM->value,
            ];
            $needLevel = $needPool[array_rand($needPool)];

            $housingPool = [
                HousingStatus::RENTED->value, HousingStatus::RENTED->value,
                HousingStatus::OWNED->value, HousingStatus::HOSTED->value,
                HousingStatus::RENTED->value, HousingStatus::UNSAFE->value,
                HousingStatus::TEMPORARY->value, HousingStatus::RENTED->value,
                HousingStatus::HOMELESS->value, HousingStatus::OWNED->value,
            ];

            $hasDisability = fake()->boolean(20);
            $hasChronic = fake()->boolean(25);

            if ($demoFlag === 'critical') {
                $familyMembers = fake()->numberBetween(8, 12);
                $children = fake()->numberBetween(3, 6);
                $dependents = fake()->numberBetween(2, 5);
                $income = fake()->numberBetween(0, 300);
                $hasDisability = true;
                $hasChronic = true;
                $housingStatus = HousingStatus::UNSAFE->value;
                $needLevel = NeedLevel::CRITICAL->value;
            }

            if ($income <= 800 && $demoFlag !== 'critical') {
                $housingPool = [HousingStatus::HOSTED->value, HousingStatus::RENTED->value, HousingStatus::TEMPORARY->value];
            }

            // For low income families, prefer hosted/temporary
            if ($income <= 500 && $demoFlag !== 'critical') {
                $housingPool = [HousingStatus::HOSTED->value, HousingStatus::HOMELESS->value, HousingStatus::UNSAFE->value, HousingStatus::TEMPORARY->value];
            }
            $housingStatus = $housingPool[array_rand($housingPool)];

            $beneficiary = Beneficiary::create([
                'user_id' => $user->id,
                'national_id' => $nationalId,
                'phone' => $phone,
                'alternative_phone' => fake()->boolean(60) ? $phone : null,
                'date_of_birth' => fake()->dateTimeBetween('-65 years', '-18 years'),
                'gender' => $gender,
                'social_status' => $socialStatus,
                'family_members_count' => $familyMembers,
                'children_count' => $children,
                'dependents_count' => $dependents,
                'monthly_income' => $income,
                'has_disability' => $hasDisability,
                'disability_details' => $hasDisability ? $this->disabilities[array_rand($this->disabilities)] : null,
                'has_chronic_disease' => $hasChronic,
                'chronic_disease_details' => $hasChronic ? $this->chronicDiseases[array_rand($this->chronicDiseases)] : null,
                'housing_status' => $housingStatus,
                'need_level' => $needLevel,
                'governorate' => 'الخليل',
                'city' => $city,
                'address' => $this->areas[array_rand($this->areas)],
                'notes' => fake()->boolean(30)
                    ? 'ملاحظة: ' . $this->infoNotes[array_rand($this->infoNotes)]
                    : null,
                'is_active' => true,
            ]);

            $beneficiary->demo_flag = $demoFlag ?? $i;
            $beneficiaries[] = $beneficiary;
        }

        return $beneficiaries;
    }

PHP;
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code, FILE_APPEND);
echo "Part 4 written. Bytes: " . strlen($code) . "\n";
