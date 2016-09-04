<?php
declare(strict_types=1);
namespace Viserio\Exception\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Exception extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'exception';
    }
}
