<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Command;

use Symfony\Component\Console\Helper\Table;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Cron\Schedule;

class CronListCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'cron:list';

    /**
     * {@inheritdoc}
     */
    protected $description = 'List all Cron jobs';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $container = $this->getContainer();
        $cronJobs  = $container->get(Schedule::class)->getCronJobs();

        $table = new Table($this->getOutput());
        $table->setHeaders(['Jobname', 'Expression', 'Summary']);

        $rows = [];

        foreach ($cronJobs as $cronJob) {
            $rows[] = [
                $cronJob->getCommand(),
                $cronJob->getExpression(),
                $cronJob->getSummaryForDisplay(),
            ];
        }

        $table->setRows($rows);

        $table->render();
    }
}
