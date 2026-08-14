<?php

namespace App\Domains\AidRequests\Rules;

class AttachmentValidationRules
{
    public const MAX_FILE_SIZE_KB = 5120;

    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    public static function fileRules(): array
    {
        return [
            'required',
            'file',
            'max:' . self::MAX_FILE_SIZE_KB,
            'mimes:pdf,jpg,jpeg,png',
            'mimetypes:' . implode(',', self::ALLOWED_MIMES),
        ];
    }
}
