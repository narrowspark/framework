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

use PHPUnit\Framework\Assert;
use Viserio\Contract\Cron\CronJob as CronJobContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;

return [
    new class() implements CronJobContract {
        /**
         * {@inheritdoc}
         */
        public static function register(ScheduleContract $schedule): void
        {
            Assert::assertInstanceOf(ScheduleContract::class, $schedule);
        }
    },
    static function (ScheduleContract $schedule): void {
        Assert::assertInstanceOf(ScheduleContract::class, $schedule);
    },
];
