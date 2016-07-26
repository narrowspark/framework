<?php
declare(strict_types=1);
namespace Viserio\Cookie\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class RequestCookie extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'request-cookie';
    }
}
