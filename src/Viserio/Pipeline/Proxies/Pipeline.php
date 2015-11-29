<?php
namespace Viserio\Pipeline\Proxies;

use Viserio\Support\StaticalProxyManager;

class Pipeline extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'pipeline';
    }
}
