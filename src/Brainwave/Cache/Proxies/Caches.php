<?php

namespace Brainwave\Cache\Proxies;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Support\StaticalProxyManager;

/**
 * Caches.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
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
