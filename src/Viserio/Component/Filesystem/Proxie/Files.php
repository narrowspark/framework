<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Proxie;

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
        return 'files';
    }
}
