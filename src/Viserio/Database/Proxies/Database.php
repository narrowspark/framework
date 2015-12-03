<?php
namespace Viserio\Database\Proxies;

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
