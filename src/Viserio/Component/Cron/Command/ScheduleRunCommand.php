<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Cron;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ScheduleRunCommand extends AbstractCommand implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'cron:run';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Run Cron jobs';

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'env',
            'maintenance',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param \Viserio\Component\Cron\Schedule $schedule
     */
    public function handle(Schedule $schedule): int
    {
        $options   = self::resolveOptions($this->getContainer()->get('config'));
        $cronJobs  = $schedule->dueCronJobs(
            $options['env'],
            $options['maintenance']
        );

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

        if (\count($cronJobs) === 0 || $cronJobsRan === 0) {
            $this->info('No scheduled commands are ready to run.');
        }

        return 0;
    }
}
