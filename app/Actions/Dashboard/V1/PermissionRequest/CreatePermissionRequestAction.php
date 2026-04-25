<?php

namespace Modules\Employee\Actions\Dashboard\V1\PermissionRequest;

use Modules\Employee\Models\PermissionRequest;

class CreatePermissionRequestAction
{
    public function execute(array $data): PermissionRequest
    {
        return PermissionRequest::create($data);
    }
}
