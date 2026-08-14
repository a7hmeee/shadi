<?php

namespace App\Domains\Beneficiaries\DTOs;

use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use Illuminate\Support\Carbon;

final readonly class BeneficiaryData
{
    public function __construct(
        public int $userId,
        public string $nationalId,
        public string $phone,
        public ?string $alternativePhone,
        public ?Carbon $dateOfBirth,
        public ?string $gender,
        public SocialStatus $socialStatus,
        public int $familyMembersCount,
        public int $childrenCount,
        public int $dependentsCount,
        public float $monthlyIncome,
        public bool $hasDisability,
        public ?string $disabilityDetails,
        public bool $hasChronicDisease,
        public ?string $chronicDiseaseDetails,
        public ?HousingStatus $housingStatus,
        public NeedLevel $needLevel,
        public ?string $governorate,
        public ?string $city,
        public ?string $address,
        public ?string $notes,
        public bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) ($data['user_id'] ?? 0),
            nationalId: (string) ($data['national_id'] ?? ''),
            phone: (string) ($data['phone'] ?? ''),
            alternativePhone: $data['alternative_phone'] ?? null,
            dateOfBirth: isset($data['date_of_birth']) && $data['date_of_birth'] !== null && $data['date_of_birth'] !== ''
                ? (is_string($data['date_of_birth']) ? Carbon::parse($data['date_of_birth']) : $data['date_of_birth'])
                : null,
            gender: $data['gender'] ?? null,
            socialStatus: SocialStatus::from($data['social_status'] ?? SocialStatus::SINGLE->value),
            familyMembersCount: (int) ($data['family_members_count'] ?? 1),
            childrenCount: (int) ($data['children_count'] ?? 0),
            dependentsCount: (int) ($data['dependents_count'] ?? 0),
            monthlyIncome: (float) ($data['monthly_income'] ?? 0),
            hasDisability: (bool) ($data['has_disability'] ?? false),
            disabilityDetails: $data['disability_details'] ?? null,
            hasChronicDisease: (bool) ($data['has_chronic_disease'] ?? false),
            chronicDiseaseDetails: $data['chronic_disease_details'] ?? null,
            housingStatus: isset($data['housing_status']) && $data['housing_status'] !== null && $data['housing_status'] !== ''
                ? HousingStatus::from($data['housing_status'])
                : null,
            needLevel: NeedLevel::from($data['need_level'] ?? NeedLevel::LOW->value),
            governorate: $data['governorate'] ?? null,
            city: $data['city'] ?? null,
            address: $data['address'] ?? null,
            notes: $data['notes'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'national_id' => $this->nationalId,
            'phone' => $this->phone,
            'alternative_phone' => $this->alternativePhone,
            'date_of_birth' => $this->dateOfBirth?->format('Y-m-d'),
            'gender' => $this->gender,
            'social_status' => $this->socialStatus->value,
            'family_members_count' => $this->familyMembersCount,
            'children_count' => $this->childrenCount,
            'dependents_count' => $this->dependentsCount,
            'monthly_income' => $this->monthlyIncome,
            'has_disability' => $this->hasDisability,
            'disability_details' => $this->disabilityDetails,
            'has_chronic_disease' => $this->hasChronicDisease,
            'chronic_disease_details' => $this->chronicDiseaseDetails,
            'housing_status' => $this->housingStatus?->value,
            'need_level' => $this->needLevel->value,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'address' => $this->address,
            'notes' => $this->notes,
            'is_active' => $this->isActive,
        ];
    }
}
