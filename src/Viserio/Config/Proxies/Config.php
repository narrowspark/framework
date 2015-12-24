<?php
namespace Viserio\Config\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Config extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'config';
    }
}
