<?php
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class YamlParser extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'yaml.parser';
    }
}
