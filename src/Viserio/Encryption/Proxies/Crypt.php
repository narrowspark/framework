<?php

declare(strict_types=1);
namespace Viserio\Encryption\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Crypt extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'encrypter';
    }
}
