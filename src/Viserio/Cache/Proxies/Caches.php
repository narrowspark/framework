<?php
namespace Viserio\Cache\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Caches extends StaticalProxy
{
    public static function make($key)
    {
        return self::$container['caches'][$key];
    }

    protected static function getFacadeAccessor()
    {
        return 'caches';
    }
}
