<?php
namespace Viserio\Encryption\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Crypt.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Crypt extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'encrypter';
    }
}
