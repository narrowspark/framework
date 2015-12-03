<?php
namespace Viserio\Support\Proxies;

use Viserio\Support\StaticalProxyManager;

class Autoloader extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'autoloader';
    }
}
