<?php
declare(strict_types=1);
namespace Viserio\Config\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Config extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'config';
    }
}
