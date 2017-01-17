<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Commands;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Cron\Schedule;

class ScheduleRunCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Cron jobs';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $container = $this->getContainer();
        $cronJobs  = $container->get(Schedule::class)->dueCronJobs(
            $container->get(RepositoryContract::class)->get('app.env'),
            $container->get(RepositoryContract::class)->get('app.maintenance')
        );

        $cronJobsRan = 0;

        foreach ($cronJobs as $cronJob) {
            if (! $cronJob->filtersPass()) {
                continue;
            }

            $this->line('<info>Running scheduled command:</info> ' . $cronJob->getSummaryForDisplay());

            $cronJob->run();

            ++$cronJobsRan;
        }

        if (count($cronJobs) === 0 || $cronJobsRan === 0) {
            $this->info('No scheduled commands are ready to run.');
        }
    }
}
