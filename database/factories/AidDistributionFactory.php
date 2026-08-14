<?php

namespace Database\Factories;

use App\Domains\AidRequests\Models\AidDistribution;
use App\Domains\AidRequests\Models\AidRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AidDistribution>
 */
class AidDistributionFactory extends Factory
{
    protected $model = AidDistribution::class;

    public function definition(): array
    {
        return [
            'aid_request_id' => AidRequest::factory(),
            'distributed_by' => User::factory(),
            'delivered_to_name' => fake()->name(),
            'delivered_to_national_id' => fake()->optional()->numerify('##############'),
            'delivery_method' => fake()->optional()->randomElement(['in_person', 'transfer', 'courier']),
            'amount' => fake()->optional()->randomFloat(2, 50, 50000),
            'delivered_at' => now(),
            'receipt_number' => fake()->optional()->regexify('RCP-\d{6}'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
