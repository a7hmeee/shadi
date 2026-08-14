<?php

namespace App\Domains\AidRequests\Models;

use App\Domains\AidRequests\Enums\AidRequestStatus;
use App\Domains\AidRequests\Enums\AidType;
use App\Domains\AidRequests\Enums\PriorityLevel;
use App\Models\User;
use Database\Factories\AidRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AidRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'beneficiary_id',
        'request_number',
        'aid_type',
        'title',
        'description',
        'status',
        'priority_level',
        'priority_score',
        'requested_amount',
        'approved_amount',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'delivered_at',
        'reviewed_by',
        'decision_by',
        'rejection_reason',
        'reviewer_notes',
        'beneficiary_notes',
        'possible_duplicate',
        'duplicate_of_request_id',
    ];

    protected function casts(): array
    {
        return [
            'aid_type' => AidType::class,
            'status' => AidRequestStatus::class,
            'priority_level' => PriorityLevel::class,
            'priority_score' => 'integer',
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'delivered_at' => 'datetime',
            'possible_duplicate' => 'boolean',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Beneficiaries\Models\Beneficiary::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function decisionMaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AidRequestAttachment::class);
    }

    public function distribution(): HasOne
    {
        return $this->hasOne(AidDistribution::class);
    }

    public function duplicatedFrom(): BelongsTo
    {
        return $this->belongsTo(AidRequest::class, 'duplicate_of_request_id');
    }

    public function possibleDuplicates(): HasMany
    {
        return $this->hasMany(AidRequest::class, 'duplicate_of_request_id');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            AidRequestStatus::SUBMITTED->value,
            AidRequestStatus::UNDER_REVIEW->value,
            AidRequestStatus::NEEDS_INFORMATION->value,
        ]);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority_level', [
            PriorityLevel::HIGH->value,
            PriorityLevel::CRITICAL->value,
        ]);
    }

    public function scopePossibleDuplicates($query)
    {
        return $query->where('possible_duplicate', true);
    }

    protected static function newFactory(): AidRequestFactory
    {
        return AidRequestFactory::new();
    }
}