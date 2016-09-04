<?php
declare(strict_types=1);
namespace Viserio\Cache\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Caches extends StaticalProxy
{
    public static function make($key)
    {
        return self::$container['caches'][$key];
    }

    public static function getInstanceIdentifier()
    {
        return 'caches';
    }
}
