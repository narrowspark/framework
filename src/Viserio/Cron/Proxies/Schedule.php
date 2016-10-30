<?php
declare(strict_types=1);
namespace Viserio\Cron\Proxies;

use Viserio\Cron\Schedule as CronSchedule;
use Viserio\StaticalProxy\StaticalProxy;

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
