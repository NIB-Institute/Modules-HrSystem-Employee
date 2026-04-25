<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\TrashController;
use Illuminate\Database\Eloquent\Model;
use Modules\Employee\Models\Employee;

class EmployeeTrashController extends TrashController
{
    protected function getModelClass(): string
    {
        return Employee::class;
    }

    protected function getTrashPagePath(): string
    {
        return 'employee::Dashboard/V1/Employee/Trash';
    }

    protected function getRoutePrefix(): string
    {
        return 'employee.employees.trash';
    }

    protected function getEntityLabel(): string
    {
        return 'Employee';
    }

    protected function getEntityLabelPlural(): string
    {
        return 'Employees';
    }

    protected function toTrashItem(Model $model): array
    {
        return [
            'id' => $model->getKey(),
            'uuid' => $model->uuid ?? null,
            'display_name' => trim($model->first_name . ' ' . $model->last_name),
            'type' => $this->getEntityLabel(),
            'deleted_at' => $model->deleted_at?->toISOString(),
            'created_at' => $model->created_at?->toISOString(),
        ];
    }
}
