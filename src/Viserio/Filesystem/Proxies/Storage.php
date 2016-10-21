<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

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
