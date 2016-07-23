<?php

declare(strict_types=1);
namespace Viserio\Crypter\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

/**
 * Hash.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class Hash extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'hash';
    }
}
