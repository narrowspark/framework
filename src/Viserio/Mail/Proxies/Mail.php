<?php

declare(strict_types=1);
namespace Viserio\Mail\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Mail extends StaticalProxy
{
    protected static function getFacadeAccessor()
    {
        return 'mailer';
    }
}
