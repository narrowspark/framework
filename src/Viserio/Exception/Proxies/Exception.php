<?php

declare(strict_types=1);
namespace Viserio\Exception\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Exception extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'exception';
    }
}
