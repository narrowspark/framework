<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Proxy;

use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Provider\Doctrine\Connection;

class DBAL extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return Connection::class;
    }
}
