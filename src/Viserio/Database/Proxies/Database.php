<?php
namespace Viserio\Database\Proxies;

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
 * @version     0.10.0
 */

use Viserio\Support\StaticalProxyManager;

/**
 * Database.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1
 */
class Database extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
