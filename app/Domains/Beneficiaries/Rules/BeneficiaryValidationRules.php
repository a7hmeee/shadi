<?php

namespace App\Domains\Beneficiaries\Rules;

use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use Illuminate\Validation\Validator;

class BeneficiaryValidationRules
{
    public static function forCreate(): array
    {
        return self::rules(null);
    }

    public static function forUpdate(?int $beneficiaryId): array
    {
        return self::rules($beneficiaryId);
    }

    private static function rules(?int $beneficiaryId): array
    {
        $uniqueRule = 'unique:beneficiaries,national_id';
        if ($beneficiaryId !== null) {
            $uniqueRule .= ',' . $beneficiaryId;
        }

        return [
            'user_id' => 'required|exists:users,id',
            'national_id' => ['required', 'string', $uniqueRule],
            'phone' => 'required|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'social_status' => ['required', 'string', 'in:' . implode(',', array_column(SocialStatus::cases(), 'value'))],
            'family_members_count' => 'required|integer|min:1|max:50',
            'children_count' => 'required|integer|min:0',
            'dependents_count' => 'nullable|integer|min:0|max:50',
            'monthly_income' => 'nullable|numeric|min:0',
            'has_disability' => 'boolean',
            'disability_details' => 'nullable|string',
            'has_chronic_disease' => 'boolean',
            'chronic_disease_details' => 'nullable|string',
            'housing_status' => ['nullable', 'string', 'in:' . implode(',', array_column(HousingStatus::cases(), 'value'))],
            'need_level' => ['required', 'string', 'in:' . implode(',', array_column(NeedLevel::cases(), 'value'))],
            'governorate' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public static function withCrossFieldValidation(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $v->getData();
            if (isset($data['children_count'], $data['family_members_count'])) {
                if ((int) $data['children_count'] > (int) $data['family_members_count']) {
                    $v->errors()->add('children_count', 'عدد الأطفال لا يمكن أن يتجاوز عدد أفراد الأسرة.');
                }
            }
            if (isset($data['dependents_count'], $data['family_members_count'])) {
                if ((int) $data['dependents_count'] > (int) $data['family_members_count']) {
                    $v->errors()->add('dependents_count', 'عدد الرهين لا يمكن أن يتجاوز عدد أفراد الأسرة.');
                }
            }
        });
    }
}
