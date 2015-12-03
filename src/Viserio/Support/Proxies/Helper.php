<?php
namespace Viserio\Support\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Helper.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2
 */
class Helper extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'helper';
    }
}
