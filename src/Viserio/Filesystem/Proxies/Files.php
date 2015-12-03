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
class Files extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'files';
    }
}
