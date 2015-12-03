<?php
namespace Viserio\Database\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Query.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1
 */
class Query extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'db.query';
    }
}
