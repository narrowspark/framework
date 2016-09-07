<?php
declare(strict_types=1);
namespace Viserio\Queue\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Queue extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'queue';
    }
}
