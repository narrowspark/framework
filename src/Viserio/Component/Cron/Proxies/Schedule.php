<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Proxies;

use Viserio\Component\Cron\Schedule as CronSchedule;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Schedule extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return CronSchedule::class;
    }
}
