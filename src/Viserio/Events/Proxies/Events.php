<?php
declare(strict_types=1);
namespace Viserio\Events\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Events extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'events';
    }
}
