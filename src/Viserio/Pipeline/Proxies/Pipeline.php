<?php

declare(strict_types=1);
namespace Viserio\Pipeline\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Pipeline extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'pipeline';
    }
}
