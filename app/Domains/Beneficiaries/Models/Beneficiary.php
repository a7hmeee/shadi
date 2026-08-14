<?php

namespace App\Domains\Beneficiaries\Models;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Beneficiaries\Enums\HousingStatus;
use App\Domains\Beneficiaries\Enums\NeedLevel;
use App\Domains\Beneficiaries\Enums\SocialStatus;
use App\Models\User;
use Database\Factories\BeneficiaryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficiary extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'national_id',
        'phone',
        'alternative_phone',
        'date_of_birth',
        'gender',
        'social_status',
        'family_members_count',
        'children_count',
        'dependents_count',
        'monthly_income',
        'has_disability',
        'disability_details',
        'has_chronic_disease',
        'chronic_disease_details',
        'housing_status',
        'need_level',
        'governorate',
        'city',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'social_status' => SocialStatus::class,
            'housing_status' => HousingStatus::class,
            'need_level' => NeedLevel::class,
            'monthly_income' => 'decimal:2',
            'has_disability' => 'boolean',
            'has_chronic_disease' => 'boolean',
            'is_active' => 'boolean',
            'family_members_count' => 'integer',
            'children_count' => 'integer',
            'dependents_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aidRequests(): HasMany
    {
        return $this->hasMany(AidRequest::class);
    }

    public function fullAddress(): string
    {
        $parts = array_filter([
            $this->governorate,
            $this->city,
            $this->address,
        ]);

        return implode(' - ', $parts);
    }

    protected static function newFactory(): BeneficiaryFactory
    {
        return BeneficiaryFactory::new();
    }}
