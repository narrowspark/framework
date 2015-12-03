<?php
namespace Viserio\Filesystem\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Files.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
 */
class Storage extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}
