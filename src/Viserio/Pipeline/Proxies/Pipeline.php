<?php
declare(strict_types=1);
namespace Viserio\Pipeline\Proxies;

use Viserio\StaticalProxy\StaticalProxy;

class Pipeline extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return 'pipeline';
    }
}
