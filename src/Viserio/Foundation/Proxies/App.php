<?php
declare(strict_types=1);
namespace Viserio\Foundation\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class App extends StaticalProxy
{
    public static function make($key)
    {
        return self::$container[$key];
    }

    public static function getInstanceIdentifier()
    {
        return self::$container;
    }
}
