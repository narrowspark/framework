<?php
declare(strict_types=1);
namespace Viserio\Foundation\Proxies;

class App extends StaticalProxy
{
    public static function make($key)
    {
        return self::$container[$key];
    }

    protected static function getFacadeAccessor()
    {
        return self::$container;
    }
}
