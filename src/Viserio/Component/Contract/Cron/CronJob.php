<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cron;

use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;

interface CronJob
{
    /**
     * Register the cron job to the schedule.
     *
     * @param \Viserio\Component\Contract\Cron\Schedule $schedule
     *
     * @return void
     */
    public static function register(ScheduleContract $schedule): void;
}
