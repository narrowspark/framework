<?php
namespace Viserio\Events\Proxies;

use Viserio\Support\StaticalProxyManager;

class Events extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}
