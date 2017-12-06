<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;

final class NullConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        return new NullAdapter();
    }
}
