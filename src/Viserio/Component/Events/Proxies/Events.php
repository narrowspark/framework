<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Events extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'events';
    }
}
