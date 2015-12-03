<?php
namespace Viserio\Exception\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Exception.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
 */
class Exception extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'exception';
    }
}
