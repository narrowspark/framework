<?php
namespace Viserio\Cache\Proxies;

use Viserio\Support\StaticalProxyManager;

class Caches extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'caches';
    }

    public static function make($key)
    {
        return self::$container['caches'][$key];
    }
}
