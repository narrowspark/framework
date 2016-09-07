<?php
declare(strict_types=1);
namespace Viserio\Mail\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Mail extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'mailer';
    }
}
