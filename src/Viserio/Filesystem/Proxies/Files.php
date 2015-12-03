<?php
namespace Viserio\Filesystem\Proxies;

use Viserio\Support\StaticalProxyManager;

class Files extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'files';
    }
}
