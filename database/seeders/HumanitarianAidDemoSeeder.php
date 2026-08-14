<?php

namespace Database\Seeders;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Models\Beneficiary;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HumanitarianAidDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->warn('HumanitarianAidDemoSeeder is not intended for production.');

            return;
        }

        Beneficiary::factory()
            ->count(10)
            ->create()
            ->each(function (Beneficiary $beneficiary) {
                $requestCount = fake()->numberBetween(1, 3);

                AidRequest::factory()
                    ->count($requestCount)
                    ->for($beneficiary)
                    ->state(fn (array $attributes) => [
                        'status' => AidRequestStatus::cases()[fake()->numberBetween(0, 7)]->value,
                        'priority_level' => PriorityLevel::cases()[fake()->numberBetween(0, 3)]->value,
                    ])
                    ->create();
            });

        $this->markPossibleDuplicates();
    }

    protected function markPossibleDuplicates(): void
    {
        AidRequest::where('possible_duplicate', false)
            ->inRandomOrder()
            ->limit(2)
            ->update(['possible_duplicate' => true]);
    }
}