<?php
declare(strict_types=1);
namespace Viserio\Session\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Sessions extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'session';
    }
}
