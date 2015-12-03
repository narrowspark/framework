<?php
namespace Viserio\Application\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Services.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Services extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'services';
    }
}
