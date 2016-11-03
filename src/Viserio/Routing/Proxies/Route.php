<?php
declare(strict_types=1);
namespace Viserio\Routing\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Route extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'route';
    }
}
