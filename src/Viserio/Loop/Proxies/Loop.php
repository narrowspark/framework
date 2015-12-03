<?php
namespace Viserio\Loop\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Loop.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class Loop extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'loop';
    }
}
