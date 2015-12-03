<?php
namespace Viserio\Support\Proxies;

use Viserio\Support\StaticalProxyManager;

class Helper extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'helper';
    }
}
