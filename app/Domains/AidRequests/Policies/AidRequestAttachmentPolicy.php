<?php

namespace App\Domains\AidRequests\Policies;

use App\Domains\AidRequests\Models\AidRequestAttachment;
use App\Domains\Identity\Enums\PermissionName;
use App\Models\User;

class AidRequestAttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::AID_REQUEST_ATTACHMENTS_VIEW->value);
    }

    public function view(User $user, AidRequestAttachment $attachment): bool
    {
        return $user->can(PermissionName::AID_REQUEST_ATTACHMENTS_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::AID_REQUEST_ATTACHMENTS_UPLOAD->value);
    }

    public function delete(User $user, AidRequestAttachment $attachment): bool
    {
        return $user->can(PermissionName::AID_REQUEST_ATTACHMENTS_DELETE->value);
    }

    public function verify(User $user, AidRequestAttachment $attachment): bool
    {
        return $user->can(PermissionName::AID_REQUEST_ATTACHMENTS_VERIFY->value);
    }
}
