<?php
namespace Viserio\Http\Proxies;

use Viserio\Support\StaticalProxyManager;

class Request extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}
