<?php
namespace Viserio\Console\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Console.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Console extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'console';
    }
}
