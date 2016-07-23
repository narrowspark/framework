<?php

declare(strict_types=1);
namespace Viserio\Cache\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Cache extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
