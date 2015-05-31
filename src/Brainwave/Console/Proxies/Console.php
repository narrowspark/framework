<?php

namespace Brainwave\Console\Proxies;

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
 * Console.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Console extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'console';
    }
}
