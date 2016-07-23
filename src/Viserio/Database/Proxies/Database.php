<?php

declare(strict_types=1);
namespace Viserio\Database\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Database extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
