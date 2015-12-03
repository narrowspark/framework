<?php
namespace Viserio\Routing\Proxies;

use Viserio\Support\StaticalProxyManager;

class Route extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'route';
    }
}
