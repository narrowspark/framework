<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use InvalidArgumentException;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Narrowspark\Arr\Arr;
use Viserio\Component\Contracts\Filesystem\Connector as ConnectorContract;
use ZipArchive;

class ZipConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return \League\Flysystem\ZipArchive\ZipArchiveAdapter
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
     * @return array
     */
    protected function getConfig(array $config): array
    {
        if (! array_key_exists('path', $config)) {
            throw new InvalidArgumentException('The zip connector requires path configuration.');
        }

        if (! array_key_exists('archive', $config)) {
            $config['archive'] = new ZipArchive();
        }

        if (! array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return Arr::only($config, ['path', 'archive', 'prefix']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(array $config): ZipArchiveAdapter
    {
        return new ZipArchiveAdapter($config['path'], $config['archive'], $config['prefix']);
    }
}
