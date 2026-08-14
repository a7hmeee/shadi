<?php

namespace App\Domains\AidRequests\Models;

use App\Models\User;
use Database\Factories\AidDistributionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AidDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'aid_request_id',
        'distributed_by',
        'delivered_to_name',
        'delivered_to_national_id',
        'delivery_method',
        'amount',
        'delivered_at',
        'receipt_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'delivered_at' => 'datetime',
        ];
    }

    public function aidRequest(): BelongsTo
    {
        return $this->belongsTo(AidRequest::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    protected static function newFactory(): AidDistributionFactory
    {
        return AidDistributionFactory::new();
    }
}