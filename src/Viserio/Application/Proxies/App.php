<?php
namespace Viserio\Application\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

/**
 * App.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class App extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return self::$container;
    }

    public static function make($key)
    {
        return self::$container[$key];
    }
}
