<?php
declare(strict_types=1);
namespace Viserio\View\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class View extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'view';
    }
}
