<?php
namespace Viserio\Exception\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Exception extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'exception';
    }
}
