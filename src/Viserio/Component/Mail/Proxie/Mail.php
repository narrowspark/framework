<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Proxie;

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
        return 'mailer';
    }
}
