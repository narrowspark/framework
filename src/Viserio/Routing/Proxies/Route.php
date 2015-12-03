<?php
namespace Viserio\Routing\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Route.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Route extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'route';
    }
}
