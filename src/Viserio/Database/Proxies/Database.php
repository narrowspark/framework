<?php
declare(strict_types=1);
namespace Viserio\Database\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Database extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'db';
    }
}
