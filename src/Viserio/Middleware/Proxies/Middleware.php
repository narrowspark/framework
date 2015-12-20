<?php
namespace Viserio\Middleware\Proxies;

use Viserio\Support\StaticalProxyManager;

class Middleware extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'middleware';
    }
}
