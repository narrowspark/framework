<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Commands;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;

class ScheduleRunCommand extends Command implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use ConfigurationTrait;

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
    public function getDimensions(): iterable
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'env',
            'maintenance',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $container = $this->getContainer();

        $this->configureOptions($container);

        $cronJobs  = $container->get(Schedule::class)->dueCronJobs(
            $this->options['env'],
            $this->options['maintenance']
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
