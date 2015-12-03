<?php
namespace Viserio\Session\Proxies;

use Viserio\Support\StaticalProxyManager;

class Sessions extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'session';
    }
}
