<?php
declare(strict_types=1);
namespace Viserio\Events\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

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
