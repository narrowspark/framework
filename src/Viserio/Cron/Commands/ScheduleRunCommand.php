<?php
declare(strict_types=1);
namespace Viserio\Cron\Commands;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Console\Command\Command;
use Viserio\Contracts\Config\Manager as ManagerContract;
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
    public function getOptions(): array
    {
        return [
            ['daemon', null, InputOption::VALUE_NONE, 'Run schedule in daemon mode'],
            ['interval', null, InputOption::VALUE_REQUIRED, 'Run every interval seconds', 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $daemon = $this->option('daemon');
        $interval = $this->option('interval');

        if (! is_numeric($interval)) {
            throw new InvalidArgumentException('Interval must be a positive number');
        }

        while (true) {
            $start = time();
            $this->doScheduleDueJobs();

            if (! $daemon) {
                break;
            }

            // pause for a minimum of one second.
            $sleepTime = max(1, $interval - (time() - $start));
            sleep($sleepTime);
        }
    }

    /**
     * Trigger due jobs.
     */
    protected function doScheduleDueJobs()
    {
        $container = $this->getContainer();
        $cronJobs = $container->get(Schedule::class)->dueCronJobs(
            $container->get(ManagerContract::class)->get('app.env'),
            $container->get(ManagerContract::class)->get('app.maintenance')
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
