<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeeType;

use Illuminate\Support\Str;
use Modules\Employee\Models\EmployeeType;

class CreateEmployeeTypeAction
{
    public function execute(array $data): EmployeeType
    {
        $data['uuid'] = (string) Str::uuid();

        return EmployeeType::create($data);
    }
}
