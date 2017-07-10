<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Proxy;

use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Cookie extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return JarContract::class;
    }
}
