<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Proxy;

use Viserio\Component\Contracts\Mail\Mailer as MailerContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Mail extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return MailerContract::class;
    }
}
