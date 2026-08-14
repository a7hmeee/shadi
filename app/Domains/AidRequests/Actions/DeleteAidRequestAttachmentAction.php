<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;

class DeleteAidRequestAttachmentAction
{
    public function execute(AidRequestAttachment $attachment, User $user): void
    {
        if (! $user->can('delete', $attachment)) {
            throw new AuthorizationException('غير مصرح لك بحذف المرفقات.');
        }

        $disk = Storage::disk($attachment->disk);

        if ($disk->exists($attachment->file_path)) {
            $disk->delete($attachment->file_path);
        }

        $attachment->delete();
    }
}
