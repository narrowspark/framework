<?php
namespace Viserio\View\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * View.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class View extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
}
