<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class VerifyAidRequestAttachmentAction
{
    public function execute(AidRequestAttachment $attachment, User $user): AidRequestAttachment
    {
        if (! $user->can('verify', $attachment)) {
            throw new AuthorizationException('غير مصرح لك بتوثيق المرفقات.');
        }

        $attachment->update([
            'is_verified' => true,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        return $attachment->fresh();
    }
}
