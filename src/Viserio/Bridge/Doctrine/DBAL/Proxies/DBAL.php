<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Proxies;

use Viserio\Bridge\Doctrine\DBAL\Connection;
use Viserio\Component\StaticalProxy\StaticalProxy;

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
