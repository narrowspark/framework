<?php

declare(strict_types=1);
namespace Viserio\Application\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

/**
 * Services.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Services extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'services';
    }
}
