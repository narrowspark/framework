<?php
namespace Viserio\Connect\Proxies;

use Viserio\Support\StaticalProxyManager;

class Connect extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'connect';
    }
}
