<?php
declare(strict_types=1);
namespace Viserio\Bus\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

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
