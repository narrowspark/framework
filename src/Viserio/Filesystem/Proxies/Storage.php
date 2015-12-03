<?php
namespace Viserio\Filesystem\Proxies;

use Viserio\Support\StaticalProxyManager;

class Storage extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}
