<?php
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class PHPParser extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'php.parser';
    }
}
