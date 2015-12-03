<?php
namespace Viserio\Http\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Response.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Response extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
