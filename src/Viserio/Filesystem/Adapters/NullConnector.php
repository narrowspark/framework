<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\Adapter\NullAdapter;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

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
