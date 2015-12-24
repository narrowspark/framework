<?php
namespace Viserio\Support\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Helper extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'helper';
    }
}
