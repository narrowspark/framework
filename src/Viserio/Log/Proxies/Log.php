<?php
namespace Viserio\Log\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Log.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Log extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'logger';
    }
}
