<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Proxy;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Bus extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'bus';
    }
}
