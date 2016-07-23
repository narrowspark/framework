<?php

declare(strict_types=1);
namespace Viserio\Connect\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Connect extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'connect';
    }
}
