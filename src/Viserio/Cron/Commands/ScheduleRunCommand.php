<?php
declare(strict_types=1);
namespace Viserio\Cron\Commands;

use Viserio\Console\Command\Command;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Cron\Schedule;

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
        $cronJobs = $container->get(Schedule::class)->dueCronJobs(
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
