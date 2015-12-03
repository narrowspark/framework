<?php
namespace Viserio\Session\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Session.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Sessions extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'session';
    }
}
