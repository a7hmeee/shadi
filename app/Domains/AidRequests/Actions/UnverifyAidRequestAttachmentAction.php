<?php

namespace App\Domains\AidRequests\Actions;

use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class UnverifyAidRequestAttachmentAction
{
    public function execute(AidRequestAttachment $attachment, User $user): AidRequestAttachment
    {
        if (! $user->can('verify', $attachment)) {
            throw new AuthorizationException('غير مصرح لك بإلغاء توثيق المرفقات.');
        }

        $attachment->update([
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return $attachment->fresh();
    }
}
