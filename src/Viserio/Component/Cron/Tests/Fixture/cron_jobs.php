<?php
declare(strict_types=1);

use PHPUnit\Framework\Assert;
use Viserio\Component\Contract\Cron\CronJob as CronJobContract;
use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;

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
    function (ScheduleContract $schedule) {
        Assert::assertInstanceOf(ScheduleContract::class, $schedule);
    },
];
