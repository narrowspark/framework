<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Cron;

use Viserio\Contract\Cron\Schedule as ScheduleContract;

interface CronJob
{
    /**
     * Register the cron job to the schedule.
     *
     * @param \Viserio\Contract\Cron\Schedule $schedule
     *
     * @return void
     */
    public static function register(ScheduleContract $schedule): void;
}
