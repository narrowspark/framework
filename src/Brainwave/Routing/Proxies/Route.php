<?php
namespace Brainwave\Routing\Proxies;

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
 * Route.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Route extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'route';
    }
}
