<?php
namespace Viserio\Queue\Facades;

use Viserio\Support\StaticalProxyManager;

class Queue extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'queue';
    }
}
