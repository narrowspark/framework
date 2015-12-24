<?php
namespace Viserio\View\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

/**
 * View.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class View extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'view';
    }
}
