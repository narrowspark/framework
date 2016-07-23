<?php

declare(strict_types=1);
namespace Viserio\Middleware\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Middleware extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'middleware';
    }
}
