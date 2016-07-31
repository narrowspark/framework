<?php
declare(strict_types=1);
namespace Viserio\Database\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Query extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'db.query';
    }
}
