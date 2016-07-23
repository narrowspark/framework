<?php

declare(strict_types=1);
namespace Viserio\Config\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Config extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
