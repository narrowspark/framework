<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

class LocalConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return Local
     */
    public function connect(array $config): \League\Flysystem\Adapter\Local
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
    protected function getConfig(array $config): array
    {
        if (! array_key_exists('path', $config)) {
            throw new InvalidArgumentException('The local connector requires path configuration.');
        }

        if (! array_key_exists('write_flags', $config)) {
            $config['write_flags'] = LOCK_EX;
        }

        if (! array_key_exists('link_handling', $config)) {
            $config['link_handling'] = Local::DISALLOW_LINKS;
        }

        if (! array_key_exists('permissions', $config)) {
            $config['permissions'] = [];
        }

        return Arr::only($config, ['path', 'write_flags', 'link_handling', 'permissions']);
    }

    /**
     * Get the local adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\Adapter\Local
     */
    protected function getAdapter(array $config): \League\Flysystem\Adapter\Local
    {
        return new Local($config['path'], $config['write_flags'], $config['link_handling'], $config['permissions']);
    }
}
