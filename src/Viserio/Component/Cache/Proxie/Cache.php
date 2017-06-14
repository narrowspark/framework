<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Proxie;

use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Cache extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return CacheItemPoolInterface::class;
    }
}
