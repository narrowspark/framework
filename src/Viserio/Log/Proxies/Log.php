<?php
namespace Viserio\Log\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Log extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'logger';
    }
}
