<?php

namespace Modules\Employee\Actions\Dashboard\V1\EmployeePlan;

use Modules\Employee\Enums\EmployeePlanEnum;

class GetEmployeePlanCreateDataAction
{
    public function execute(): array
    {
        return [
            'priorities'      => EmployeePlanEnum::PRIORITIES,
            'scheduleModes'   => EmployeePlanEnum::SCHEDULE_MODES,
            'recurrenceTypes' => EmployeePlanEnum::RECURRENCE_TYPES,
            'statuses'        => EmployeePlanEnum::STATUSES,
        ];
    }
}
