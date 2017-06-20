<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Proxy;

use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Events extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return EventManagerContract::class;
    }
}
