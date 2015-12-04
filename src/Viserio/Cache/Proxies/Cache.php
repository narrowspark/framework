<?php
namespace Viserio\Cache\Proxies;

use Viserio\Support\StaticalProxyManager;

class Cache extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
