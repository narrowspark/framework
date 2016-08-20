<?php
declare(strict_types=1);
namespace Viserio\Support\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Env extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'env';
    }
}
