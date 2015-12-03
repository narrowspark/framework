<?php
namespace Viserio\Database\Proxies;

use Viserio\Support\StaticalProxyManager;

class Database extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
