<?php

namespace App\Domains\AidRequests\Policies;

use App\Domains\AidRequests\Models\AidRequest;
use App\Domains\Identity\Enums\PermissionName;
use App\Models\User;

class AidRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_VIEW->value);
    }

    public function view(User $user, AidRequest $aidRequest): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_CREATE->value);
    }

    public function update(User $user, AidRequest $aidRequest): bool
    {
        // Locked statuses cannot be edited by anyone.
        if ($aidRequest->status->isFinal() || $aidRequest->status === \App\Domains\AidRequests\Enums\AidRequestStatus::APPROVED) {
            return false;
        }

        return $user->can(PermissionName::AID_REQUESTS_VIEW->value)
            && $user->can(PermissionName::AID_REQUESTS_REVIEW->value);
    }

    public function delete(User $user, AidRequest $aidRequest): bool
    {
        // aid_requests.delete permission does not exist in the current design.
        // Deletion is disabled in this phase (MVP).
        return false;
    }

    public function restore(User $user, AidRequest $aidRequest): bool
    {
        return false;
    }

    public function forceDelete(User $user, AidRequest $aidRequest): bool
    {
        return false;
    }

    public function review(User $user, AidRequest $aidRequest): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_REVIEW->value);
    }

    public function approve(User $user, AidRequest $aidRequest): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_APPROVE->value);
    }

    public function reject(User $user, AidRequest $aidRequest): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_REJECT->value);
    }

    public function markDelivered(User $user, AidRequest $aidRequest): bool
    {
        return $user->can(PermissionName::AID_REQUESTS_MARK_DELIVERED->value);
    }
}
