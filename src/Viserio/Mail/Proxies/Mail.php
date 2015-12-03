<?php
namespace Viserio\Mail\Proxies;

use Viserio\Support\StaticalProxyManager;

/**
 * Mail.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class Mail extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
