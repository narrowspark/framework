<?php
namespace Viserio\Filesystem\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Files extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'files';
    }
}
