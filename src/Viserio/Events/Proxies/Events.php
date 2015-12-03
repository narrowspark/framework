<?php
namespace Viserio\Events\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Events.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Events extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}
