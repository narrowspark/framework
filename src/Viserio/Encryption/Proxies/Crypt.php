<?php
namespace Viserio\Encryption\Proxies;

use Viserio\Support\StaticalProxyManager;

class Crypt extends StaticalProxyManager
{
    protected static function getFacadeAccessor()
    {
        return 'encrypter';
    }
}
