<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Files extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'files';
    }
}
