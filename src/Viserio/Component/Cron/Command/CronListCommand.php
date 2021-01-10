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

use Symfony\Component\Console\Helper\Table;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Cron\Cron;
use Viserio\Component\Cron\Schedule;

class CronListCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'cron:list';

    /**
     * {@inheritdoc}
     */
    protected $description = 'List all Cron jobs';

    /**
     * {@inheritdoc}
     */
    public function handle(Schedule $schedule): int
    {
        $cronJobs = $schedule->getCronJobs();

        $table = new Table($this->getOutput());
        $table->setHeaders(['Jobname', 'Expression', 'Summary']);

        $rows = [];

        /** @var Cron $cronJob */
        foreach ($cronJobs as $cronJob) {
            $rows[] = [
                $cronJob->getCommand(),
                $cronJob->getExpression(),
                $cronJob->getSummaryForDisplay(),
            ];
        }

        $table->setRows($rows);

        $table->render();

        return 0;
    }
}
