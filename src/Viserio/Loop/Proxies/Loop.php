<?php
namespace Viserio\Loop\Proxies;

use Viserio\Support\StaticalProxyManager;

class Loop extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'loop';
    }
}
