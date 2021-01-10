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

namespace Viserio\Component\Cron\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Cron\Cron;
use Viserio\Component\Cron\Schedule;

class ScheduleRunCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'cron:run';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Run Cron jobs';

    /** @var string */
    private string $env;

    /** @var bool */
    private bool $maintenance;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $env, bool $maintenance)
    {
        parent::__construct();

        $this->env = $env;
        $this->maintenance = $maintenance;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Schedule $schedule): int
    {
        $cronJobs = $schedule->dueCronJobs($this->env, $this->maintenance);

        $cronJobsRan = 0;

        /** @var Cron $cronJob */
        foreach ($cronJobs as $cronJob) {
            if (! $cronJob->filtersPass()) {
                continue;
            }

            $this->line('<info>Running scheduled command:</info> ' . $cronJob->getSummaryForDisplay());

            $cronJob->run();

            $cronJobsRan++;
        }

        if ($cronJobsRan === 0 || \count($cronJobs) === 0) {
            $this->info('No scheduled commands are ready to run.');
        }

        return 0;
    }
}
