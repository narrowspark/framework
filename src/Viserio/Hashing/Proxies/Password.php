<?php
namespace Viserio\Crypter\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Crypt.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Password extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'password';
    }
}
