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
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;

class ScheduleRunCommand extends AbstractCommand implements RequiresComponentConfigContract,
    RequiresMandatoryOptionContract
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
        $options = self::resolveOptions($this->getContainer()->get('config'));
        $cronJobs = $schedule->dueCronJobs(
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
