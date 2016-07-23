<?php

declare(strict_types=1);
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
    public static function make($key)
    {
        return self::$container[$key];
    }

    protected static function getFacadeAccessor()
    {
        return self::$container;
    }
}
