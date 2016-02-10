<?php
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class JsonParser extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'json.parser';
    }
}
