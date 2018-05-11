<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;
use ZipArchive;

class ZipConnector implements ConnectorContract
{
    use GetSelectedConfigTrait;

    /**
     * {@inheritdoc}
     *
     * @return \League\Flysystem\ZipArchive\ZipArchiveAdapter
     */
    public function connect(array $config): AdapterInterface
    {
        $config = $this->getConfig($config);

        return new ZipArchiveAdapter(
            $config['path'],
            $config['archive'],
            $config['prefix']
        );
    }

    /**
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return string[]
     */
    private function getConfig(array $config): array
    {
        if (! \array_key_exists('path', $config)) {
            throw new InvalidArgumentException('The zip connector requires path configuration.');
        }

        if (! \array_key_exists('archive', $config)) {
            $config['archive'] = new ZipArchive();
        }

        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return self::getSelectedConfig($config, ['path', 'archive', 'prefix']);
    }
}
