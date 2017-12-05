<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem as Vfs;

class VfsConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     *
     * @return \League\Flysystem\Vfs\VfsAdapter
     */
    public function connect(array $config): AdapterInterface
    {
        $client = $this->getClient($config);

        return $this->getAdapter($client, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @return \VirtualFileSystem\FileSystem
     */
    protected function getClient(array $authConfig): object
    {
        return new Vfs();
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        return $config;
    }

    /**
     * {@inheritdoc}
     *
     * @param \VirtualFileSystem\FileSystem $client
     *
     * @return \League\Flysystem\Vfs\VfsAdapter
     */
    protected function getAdapter(object $client, array $config): AdapterInterface
    {
        return new VfsAdapter($client);
    }
}
