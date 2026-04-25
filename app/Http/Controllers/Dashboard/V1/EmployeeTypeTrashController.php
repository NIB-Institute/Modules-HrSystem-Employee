<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\TrashController;
use Modules\Employee\Models\EmployeeType;

class EmployeeTypeTrashController extends TrashController
{
    protected function getModelClass(): string
    {
        return EmployeeType::class;
    }

    protected function getTrashPagePath(): string
    {
        return 'employee::Dashboard/V1/EmployeeType/Trash';
    }

    protected function getRoutePrefix(): string
    {
        return 'employee.employee-types.trash';
    }

    protected function getEntityLabel(): string
    {
        return 'Employee Type';
    }

    protected function getEntityLabelPlural(): string
    {
        return 'Employee Types';
    }
}
