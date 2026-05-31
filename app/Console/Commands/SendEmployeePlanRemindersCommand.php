<?php

namespace Modules\Employee\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Modules\Employee\Jobs\SendPlanReminderJob;
use Modules\Employee\Services\EmployeePlanReminderService;

class SendEmployeePlanRemindersCommand extends Command
{
    protected $signature = 'employee:send-plan-reminders';

    protected $description = 'Find plan assignments whose group-reminder fire-window contains now, and dispatch Telegram send jobs.';

    public function handle(EmployeePlanReminderService $service): int
    {
        $now = CarbonImmutable::now(config('app.timezone'));

        $due = $service->findDue($now);

        if (empty($due)) {
            $this->info('No reminders due at ' . $now->toDateTimeString());
            return self::SUCCESS;
        }

        foreach ($due as $row) {
            SendPlanReminderJob::dispatch(
                $row['assignment_id'],
                $row['occurrence_date'],
                $row['tier'],
            );
        }

        $this->info('Dispatched ' . count($due) . ' reminder job(s).');
        return self::SUCCESS;
    }
}
