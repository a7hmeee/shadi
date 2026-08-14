<?php

namespace Database\Factories;

use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Domains\Beneficiaries\Models\Beneficiary;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Beneficiary>
 */
class BeneficiaryFactory extends Factory
{
    protected $model = Beneficiary::class;

    public function definition(): array
    {
        $familyMembers = fake()->numberBetween(1, 8);
        $children = fake()->numberBetween(0, max(0, $familyMembers - 1));

        return [
            'user_id' => User::factory(),
            'national_id' => fake()->unique()->numerify('##############'),
            'phone' => fake()->unique()->phoneNumber(),
            'alternative_phone' => fake()->optional()->phoneNumber(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'social_status' => SocialStatus::cases()[fake()->numberBetween(0, 4)]->value,
            'family_members_count' => $familyMembers,
            'children_count' => $children,
            'dependents_count' => fake()->numberBetween(0, max(0, $familyMembers - $children)),
            'monthly_income' => fake()->randomFloat(2, 0, 5000),
            'has_disability' => fake()->boolean(20),
            'disability_details' => fake()->optional()->sentence(),
            'has_chronic_disease' => fake()->boolean(25),
            'chronic_disease_details' => fake()->optional()->sentence(),
            'housing_status' => HousingStatus::cases()[fake()->numberBetween(0, 5)]->value,
            'need_level' => NeedLevel::cases()[fake()->numberBetween(0, 3)]->value,
            'governorate' => fake()->city(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}