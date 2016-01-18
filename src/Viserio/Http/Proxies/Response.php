<?php
namespace Viserio\Http\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Response extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
