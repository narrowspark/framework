<?php
namespace Viserio\Crypter\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Password extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'password';
    }
}
