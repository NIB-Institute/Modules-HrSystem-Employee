<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Enums\EmployeePlanEnum;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanResource;
use Modules\Employee\Models\EmployeePlan;

class GetEmployeePlanEditDataAction
{
    public function execute(EmployeePlan $employeePlan): array
    {
        $employeePlan->loadCount('assignments');

        return [
            'plan'              => (new EmployeePlanResource($employeePlan))->resolve(),
            'priorities'        => EmployeePlanEnum::PRIORITIES,
            'scheduleModes'     => EmployeePlanEnum::SCHEDULE_MODES,
            'recurrenceTypes'   => EmployeePlanEnum::RECURRENCE_TYPES,
            'statuses'          => EmployeePlanEnum::STATUSES,
        ];
    }
}
