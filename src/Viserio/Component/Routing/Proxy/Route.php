<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Proxy;

use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Route extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return RouterContract::class;
    }
}
