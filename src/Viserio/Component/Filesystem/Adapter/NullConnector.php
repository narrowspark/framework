<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\NullAdapter;
use Viserio\Component\Contracts\Filesystem\Connector as ConnectorContract;

class NullConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config)
    {
        return new NullAdapter();
    }
}
