<?php
namespace Viserio\Http\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Request extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}
