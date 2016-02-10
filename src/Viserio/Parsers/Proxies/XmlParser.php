<?php
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class XmlParser extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'xml.parser';
    }
}
