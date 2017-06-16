<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Proxy;

use Viserio\Component\Filesystem\FilesystemManager;
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
        return FilesystemManager::class;
    }
}
