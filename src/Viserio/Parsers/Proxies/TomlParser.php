<?php
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class TomlParser extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'toml.parser';
    }
}
