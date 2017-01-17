<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Config extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'config';
    }
}
