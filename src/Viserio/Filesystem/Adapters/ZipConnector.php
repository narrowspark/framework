<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;
use ZipArchive;

class ZipConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return ZipArchiveAdapter
     */
    public function connect(array $config): \League\Flysystem\ZipArchive\ZipArchiveAdapter
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
     * Get the zip adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\ZipArchive\ZipArchiveAdapter
     */
    protected function getAdapter(array $config): \League\Flysystem\ZipArchive\ZipArchiveAdapter
    {
        return new ZipArchiveAdapter($config['path'], $config['archive'], $config['prefix']);
    }
}
