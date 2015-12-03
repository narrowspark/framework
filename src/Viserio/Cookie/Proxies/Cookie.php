<?php
namespace Viserio\Cookie\Proxies;

use Viserio\Support\StaticalProxyManager;

class Cookie extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'cookie';
    }
}
