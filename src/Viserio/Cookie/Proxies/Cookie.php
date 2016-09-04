<?php
declare(strict_types=1);
namespace Viserio\Cookie\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Cookie extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'cookie';
    }
}
