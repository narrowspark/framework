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

namespace Viserio\Contract\Cron;

use Viserio\Component\Cron\CallbackCron;
use Viserio\Contract\Cron\Cron as CronContract;

interface Schedule
{
    /**
     * Add a new callback cron job to the schedule.
     *
     * @param callable|string $callback
     */
    public function call($callback, array $parameters = []): CallbackCron;

    /**
     * Add a new command cron job to the schedule.
     *
     * @throws \Viserio\Contract\Cron\Exception\LogicException
     */
    public function command(string $command, array $parameters = []): CronContract;

    /**
     * Add a new executable command cron job to the schedule.
     */
    public function exec(string $command, array $parameters = []): CronContract;

    /**
     * Get all of the events on the schedule.
     */
    public function getCronJobs(): array;

    /**
     * Get all of the cron jobs on the schedule that are due.
     */
    public function dueCronJobs(string $environment, bool $isMaintenance = false): array;
}
