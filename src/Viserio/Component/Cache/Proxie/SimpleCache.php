<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Proxie;

use Psr\SimpleCache\CacheInterface;
use Viserio\Component\StaticalProxy\StaticalProxy;

class SimpleCache extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return CacheInterface::class;
    }
}
