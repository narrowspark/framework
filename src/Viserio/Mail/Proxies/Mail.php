<?php
namespace Viserio\Mail\Proxies;

use Viserio\Support\StaticalProxyManager;

class Mail extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
