<?php
namespace Viserio\Config\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Config.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Config extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
