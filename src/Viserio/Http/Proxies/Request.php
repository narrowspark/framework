<?php
namespace Viserio\Http\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Request.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Request extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}
