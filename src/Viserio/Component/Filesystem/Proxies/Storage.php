<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;

class Storage extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'filesystem';
    }
}
