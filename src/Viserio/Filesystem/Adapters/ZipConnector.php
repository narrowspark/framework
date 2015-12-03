<?php
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;
use Viserio\Support\Arr;

class ZipConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return object
     */
    public function connect(array $config)
    {
        $config = $this->getConfig($config);

        return $this->getAdapter($config);
    }

    /**
     * Get the configuration.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('path', $config)) {
            throw new \InvalidArgumentException('The zip connector requires a path.');
        }

        return Arr::only($config, ['path']);
    }

    /**
     * Get the zip adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\ZipArchive\ZipArchiveAdapter
     */
    protected function getAdapter(array $config)
    {
        return new ZipArchiveAdapter($config['path']);
    }
}
