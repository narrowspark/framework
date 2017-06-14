<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Command;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ScheduleRunCommand extends Command implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'cron:run';

    /**
     * {@inheritdoc}
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
        $options   = $this->resolveOptions($container);
        $cronJobs  = $container->get(Schedule::class)->dueCronJobs(
            $options['env'],
            $options['maintenance']
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

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }
}
