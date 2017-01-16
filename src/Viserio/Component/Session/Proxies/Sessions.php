<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Sessions extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'session';
    }
}
