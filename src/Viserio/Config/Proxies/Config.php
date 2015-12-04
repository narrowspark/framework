<?php
namespace Viserio\Config\Proxies;

use Viserio\Support\StaticalProxyManager;

class Config extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
