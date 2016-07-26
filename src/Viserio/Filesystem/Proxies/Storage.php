<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Storage extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}
