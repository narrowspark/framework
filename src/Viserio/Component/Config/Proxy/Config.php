<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Proxy;

use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Config extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return RepositoryContract::class;
    }
}
