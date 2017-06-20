<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Proxy;

use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Files extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return FilesystemContract::class;
    }
}
