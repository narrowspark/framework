<?php
declare(strict_types=1);
namespace Viserio\Parsers\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Parser extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'parser';
    }
}
