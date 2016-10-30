<?php
declare(strict_types=1);
namespace Viserio\Mail\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Mail extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'mailer';
    }
}
