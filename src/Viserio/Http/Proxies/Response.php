<?php
namespace Viserio\Http\Proxies;

use Viserio\Support\StaticalProxyManager;

class Response extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
