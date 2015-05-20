<?php

namespace Brainwave\Http\Proxies;

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
 * @version     0.9.8-dev
 */

use Brainwave\Support\StaticalProxyManager;

/**
 * Response.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Response extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
