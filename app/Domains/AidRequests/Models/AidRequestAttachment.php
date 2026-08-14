<?php

namespace App\Domains\AidRequests\Models;

use App\Domains\AidRequests\Enums\DocumentType;
use App\Models\User;
use Database\Factories\AidRequestAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AidRequestAttachment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'aid_request_id',
        'uploaded_by',
        'original_name',
        'file_name',
        'file_path',
        'disk',
        'mime_type',
        'file_size',
        'document_type',
        'description',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'file_size' => 'integer',
            'document_type' => DocumentType::class,
            'verified_at' => 'datetime',
        ];
    }

    public function aidRequest(): BelongsTo
    {
        return $this->belongsTo(AidRequest::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    protected static function newFactory(): AidRequestAttachmentFactory
    {
        return AidRequestAttachmentFactory::new();
    }
}