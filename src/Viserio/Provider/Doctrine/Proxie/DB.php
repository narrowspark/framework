<?php
declare(strict_types=1);
namespace Viserio\Provider\Doctrine\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class DB extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'db';
    }
}
