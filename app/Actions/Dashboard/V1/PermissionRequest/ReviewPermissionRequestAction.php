<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Models\PermissionRequest;

class ReviewPermissionRequestAction
{
    public function execute(
        PermissionRequest $permissionRequest,
        string $action,
        ?string $note = null,
        bool $rejectedStatus = false,
        ?string $rejectedReason = null
    ): PermissionRequest {
        if ($action === 'approve') {
            $permissionRequest->approve(auth()->id(), $note, $rejectedStatus, $rejectedReason);
        } else {
            $permissionRequest->reject(auth()->id(), $note, $rejectedStatus, $rejectedReason);
        }

        return $permissionRequest->fresh(['employee', 'reviewer']);
    }
}
