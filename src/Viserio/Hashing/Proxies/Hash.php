<?php
namespace Viserio\Crypter\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Hash.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Hash extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'hash';
    }
}
