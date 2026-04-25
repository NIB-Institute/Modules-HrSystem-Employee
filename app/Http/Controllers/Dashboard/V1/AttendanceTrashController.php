<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\TrashController;
use Illuminate\Database\Eloquent\Model;
use Modules\Employee\Models\Attendance;

class AttendanceTrashController extends TrashController
{
    protected function getModelClass(): string
    {
        return Attendance::class;
    }

    protected function getTrashPagePath(): string
    {
        return 'employee::Dashboard/V1/Attendance/Trash';
    }

    protected function getRoutePrefix(): string
    {
        return 'employee.attendances.trash';
    }

    protected function getEntityLabel(): string
    {
        return 'Attendance';
    }

    protected function getEntityLabelPlural(): string
    {
        return 'Attendances';
    }

    protected function toTrashItem(Model $model): array
    {
        $employee = $model->employee;
        $displayName = $employee
            ? trim($employee->first_name . ' ' . $employee->last_name) . ' - ' . $model->attendance_date->format('Y-m-d')
            : 'Attendance #' . $model->getKey();

        return [
            'id' => $model->getKey(),
            'uuid' => $model->uuid ?? null,
            'display_name' => $displayName,
            'type' => $this->getEntityLabel(),
            'deleted_at' => $model->deleted_at?->toISOString(),
            'created_at' => $model->created_at?->toISOString(),
        ];
    }
}
