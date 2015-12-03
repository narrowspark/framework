<?php
namespace Viserio\Application\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * App.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class App extends StaticalProxyManager
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
