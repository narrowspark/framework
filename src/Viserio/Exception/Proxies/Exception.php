<?php
namespace Viserio\Exception\Proxies;

use Viserio\Support\StaticalProxyManager;

class Exception extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'exception';
    }
}
