<?php
namespace Viserio\Pipeline\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Pipeline extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'pipeline';
    }
}
