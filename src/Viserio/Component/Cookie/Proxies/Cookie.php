<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Proxies;

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
        return 'cookie';
    }
}
