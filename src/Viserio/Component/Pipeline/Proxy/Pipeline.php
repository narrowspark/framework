<?php
declare(strict_types=1);
namespace Viserio\Component\Pipeline\Proxy;

use Viserio\Component\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Pipeline extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return PipelineContract::class;
    }
}
