<?php
declare(strict_types=1);
namespace Viserio\Session\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

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
