<?php
declare(strict_types=1);
namespace Viserio\Schedule\Proxies;

use Viserio\Cron\Schedule as ScheduleClass;
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
        return ScheduleClass::class;
    }
}
