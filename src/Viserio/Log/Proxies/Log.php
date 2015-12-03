<?php
namespace Viserio\Log\Proxies;

use Viserio\Support\StaticalProxyManager;

class Log extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'logger';
    }
}
