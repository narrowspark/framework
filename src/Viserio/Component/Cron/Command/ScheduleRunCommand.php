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
     *
     * @param \Viserio\Component\Cron\Schedule $schedule
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
