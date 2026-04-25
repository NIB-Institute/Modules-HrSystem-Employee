<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Models\PermissionRequest;

class DeletePermissionRequestAction
{
    public function execute(PermissionRequest $permissionRequest): bool
    {
        return $permissionRequest->delete();
    }
}
