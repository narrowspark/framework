<?php
declare(strict_types=1);
namespace Viserio\Http\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Request extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'request';
    }
}
