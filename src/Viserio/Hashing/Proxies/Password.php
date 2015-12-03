<?php
namespace Viserio\Crypter\Proxies;

use Viserio\Support\StaticalProxyManager;

class Password extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'password';
    }
}
