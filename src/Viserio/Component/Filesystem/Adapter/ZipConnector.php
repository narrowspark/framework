<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use ZipArchive;

class ZipConnector extends AbstractConnector
{
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
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
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

    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $authConfig): object
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(object $client, array $config): AdapterInterface
    {
    }
}
