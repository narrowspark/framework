<?php
namespace Viserio\Database\Proxies;

use Viserio\Support\StaticalProxyManager;

class Query extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'db.query';
    }
}
