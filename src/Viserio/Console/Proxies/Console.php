<?php
declare(strict_types=1);
namespace Viserio\Console\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Console extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'console';
    }
}
