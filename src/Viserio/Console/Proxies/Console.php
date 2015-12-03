<?php
namespace Viserio\Console\Proxies;

use Viserio\Support\StaticalProxyManager;

class Console extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'console';
    }
}
