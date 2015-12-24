<?php
namespace Viserio\Loop\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Loop extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'loop';
    }
}
