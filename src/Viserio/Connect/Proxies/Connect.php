<?php
declare(strict_types=1);
namespace Viserio\Connect\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Connect extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'connect';
    }
}
