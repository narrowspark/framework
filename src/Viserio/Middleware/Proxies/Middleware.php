<?php
declare(strict_types=1);
namespace Viserio\Middleware\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Middleware extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'middleware';
    }
}
