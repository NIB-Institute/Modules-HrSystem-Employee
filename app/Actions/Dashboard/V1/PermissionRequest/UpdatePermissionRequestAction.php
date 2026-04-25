<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Models\PermissionRequest;

class UpdatePermissionRequestAction
{
    public function execute(PermissionRequest $permissionRequest, array $data): PermissionRequest
    {
        $permissionRequest->update($data);
        return $permissionRequest->fresh();
    }
}
