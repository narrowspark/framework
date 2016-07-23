<?php

declare(strict_types=1);
namespace Viserio\Bus\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Bus extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'bus';
    }
}
