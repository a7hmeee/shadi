<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\DTOs\AidRequestAttachmentData;
use App\Domains\AidRequests\Enums\DocumentType;
use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\AidRequests\Rules\AttachmentValidationRules;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UploadAidRequestAttachmentAction
{
    public const DISK = 'public';

    public function execute(
        AidRequest $aidRequest,
        UploadedFile $file,
        DocumentType $documentType,
        User $uploadedBy,
        ?string $description = null,
    ): AidRequestAttachment {
        if (! $uploadedBy->can('create', AidRequestAttachment::class)) {
            throw new AuthorizationException('غير مصرح لك برفع المرفقات.');
        }

        $this->validateFile($file);

        $disk = Storage::disk(self::DISK);
        $directory = 'aid-requests/' . $aidRequest->id . '/attachments';
        $fileName = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        $storedPath = $disk->putFileAs($directory, $file, $fileName);

        if ($storedPath === false) {
            throw new RuntimeException('فشل تخزين الملف على الخادم.');
        }

        try {
            $attachment = DB::transaction(function () use ($aidRequest, $file, $documentType, $uploadedBy, $description, $storedPath, $fileName) {
                $data = new AidRequestAttachmentData(
                    aidRequestId: $aidRequest->id,
                    uploadedBy: $uploadedBy->id,
                    originalName: $file->getClientOriginalName(),
                    fileName: $fileName,
                    filePath: $storedPath,
                    disk: self::DISK,
                    mimeType: (string) $file->getMimeType(),
                    fileSize: (int) $file->getSize(),
                    documentType: $documentType,
                    description: $description,
                );

                return AidRequestAttachment::create($data->toArray());
            });
        } catch (\Throwable $e) {
            $disk->delete($storedPath);

            throw $e;
        }

        return $attachment;
    }

    private function validateFile(UploadedFile $file): void
    {
        $validator = Validator::make(
            ['file' => $file],
            ['file' => AttachmentValidationRules::fileRules()]
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
