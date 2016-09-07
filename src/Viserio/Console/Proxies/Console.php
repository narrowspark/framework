<?php
declare(strict_types=1);
namespace Viserio\Console\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Console extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'console';
    }
}
