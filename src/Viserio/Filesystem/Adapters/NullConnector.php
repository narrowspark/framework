<?php
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\Adapter\NullAdapter;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

class NullConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return NullAdapter
     */
    public function connect(array $config)
    {
        return $this->getAdapter();
    }

    /**
     * Get the null adapter.
     *
     * @return \League\Flysystem\Adapter\NullAdapter
     */
    protected function getAdapter()
    {
        return new NullAdapter();
    }
}
