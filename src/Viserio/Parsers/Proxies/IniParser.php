<?php
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class IniParser extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'ini.parser';
    }
}
