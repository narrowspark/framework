<?php
namespace Viserio\Support\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Autoloader extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'autoloader';
    }
}
