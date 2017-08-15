<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Proxy;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Password extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'password';
    }
}
