<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
