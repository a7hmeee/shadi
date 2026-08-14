<?php

namespace Database\Factories;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Models\Beneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AidRequest>
 */
class AidRequestFactory extends Factory
{
    protected $model = AidRequest::class;

    public function definition(): array
    {
        $status = AidRequestStatus::cases()[fake()->numberBetween(0, 7)];

        return [
            'beneficiary_id' => Beneficiary::factory(),
            'request_number' => fake()->unique()->regexify('[A-Z]{3}-\d{4}-\d{6}'),
            'aid_type' => AidType::cases()[fake()->numberBetween(0, 7)]->value,
            'title' => fake()->optional()->sentence(),
            'description' => fake()->paragraph(),
            'status' => $status->value,
            'priority_level' => PriorityLevel::cases()[fake()->numberBetween(0, 3)]->value,
            'priority_score' => null,
            'requested_amount' => fake()->optional()->randomFloat(2, 100, 50000),
            'approved_amount' => null,
            'submitted_at' => $status !== AidRequestStatus::DRAFT ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'reviewed_at' => in_array($status->value, ['under_review', 'approved', 'rejected']) ? fake()->dateTimeBetween('-2 months', 'now') : null,
            'approved_at' => $status === AidRequestStatus::APPROVED ? fake()->dateTimeBetween('-1 months', 'now') : null,
            'rejected_at' => $status === AidRequestStatus::REJECTED ? fake()->dateTimeBetween('-1 months', 'now') : null,
            'delivered_at' => $status === AidRequestStatus::DELIVERED ? fake()->dateTimeBetween('-1 months', 'now') : null,
            'reviewed_by' => null,
            'decision_by' => null,
            'rejection_reason' => $status === AidRequestStatus::REJECTED ? fake()->sentence() : null,
            'reviewer_notes' => fake()->optional()->sentence(),
            'beneficiary_notes' => fake()->optional()->sentence(),
            'possible_duplicate' => false,
            'duplicate_of_request_id' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AidRequestStatus::APPROVED->value,
            'approved_at' => now(),
            'reviewed_at' => now(),
            'approved_amount' => $attributes['requested_amount'] ?? fake()->randomFloat(2, 100, 50000),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AidRequestStatus::REJECTED->value,
            'rejected_at' => now(),
            'reviewed_at' => now(),
            'rejection_reason' => 'لا يستوفي الشروط المطلوبة',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AidRequestStatus::DELIVERED->value,
            'delivered_at' => now(),
            'approved_at' => now(),
            'reviewed_at' => now(),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority_level' => PriorityLevel::HIGH->value,
        ]);
    }

    public function possibleDuplicate(): static
    {
        return $this->state(fn (array $attributes) => [
            'possible_duplicate' => true,
        ]);
    }
}