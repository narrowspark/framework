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
     *
     * @param \Viserio\Component\Cron\Schedule $schedule
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
