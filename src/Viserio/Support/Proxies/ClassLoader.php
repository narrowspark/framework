<?php
declare(strict_types=1);
namespace Viserio\Support\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Autoloader extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'classloader';
    }
}
