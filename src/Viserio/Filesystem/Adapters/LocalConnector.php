<?php
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\Adapter\Local;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;
use Viserio\Support\Arr;

class LocalConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return Local
     */
    public function connect(array $config)
    {
        $config = $this->getConfig($config);

        return $this->getAdapter($config);
    }

    /**
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('path', $config)) {
            throw new \InvalidArgumentException('The local connector requires a path.');
        }

        return Arr::only($config, ['path']);
    }

    /**
     * Get the local adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\Adapter\Local
     */
    protected function getAdapter(array $config)
    {
        return new Local($config['path']);
    }
}
