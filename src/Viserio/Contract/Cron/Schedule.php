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

use Viserio\Component\Cron\CallbackCron;
use Viserio\Contract\Cron\Cron as CronContract;

interface Schedule
{
    /**
     * Add a new callback cron job to the schedule.
     *
     * @param callable|string $callback
     * @param array           $parameters
     *
     * @return \Viserio\Component\Cron\CallbackCron
     */
    public function call($callback, array $parameters = []): CallbackCron;

    /**
     * Add a new command cron job to the schedule.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @throws \Viserio\Contract\Cron\Exception\LogicException
     *
     * @return \Viserio\Contract\Cron\Cron
     */
    public function command(string $command, array $parameters = []): CronContract;

    /**
     * Add a new executable command cron job to the schedule.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return \Viserio\Contract\Cron\Cron
     */
    public function exec(string $command, array $parameters = []): CronContract;

    /**
     * Get all of the events on the schedule.
     *
     * @return array
     */
    public function getCronJobs(): array;

    /**
     * Get all of the cron jobs on the schedule that are due.
     *
     * @param string $environment
     * @param bool   $isMaintenance
     *
     * @return array
     */
    public function dueCronJobs(string $environment, bool $isMaintenance = false): array;
}
