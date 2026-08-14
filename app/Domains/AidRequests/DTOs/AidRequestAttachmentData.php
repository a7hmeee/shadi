<?php

namespace App\Domains\AidRequests\DTOs;

use App\Domains\AidRequests\Enums\DocumentType;

final readonly class AidRequestAttachmentData
{
    public function __construct(
        public int $aidRequestId,
        public int $uploadedBy,
        public string $originalName,
        public string $fileName,
        public string $filePath,
        public string $disk,
        public string $mimeType,
        public int $fileSize,
        public DocumentType $documentType,
        public ?string $description = null,
    ) {}

    public function toArray(): array
    {
        return [
            'aid_request_id' => $this->aidRequestId,
            'uploaded_by' => $this->uploadedBy,
            'original_name' => $this->originalName,
            'file_name' => $this->fileName,
            'file_path' => $this->filePath,
            'disk' => $this->disk,
            'mime_type' => $this->mimeType,
            'file_size' => $this->fileSize,
            'document_type' => $this->documentType->value,
            'description' => $this->description,
        ];
    }
}
