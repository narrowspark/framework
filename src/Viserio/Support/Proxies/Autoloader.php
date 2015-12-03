<?php
namespace Viserio\Support\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Request.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Autoloader extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'autoloader';
    }
}
