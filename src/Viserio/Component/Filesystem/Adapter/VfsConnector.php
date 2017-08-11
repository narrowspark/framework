<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem as Vfs;

class VfsConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): object
    {
        $client = $this->getClient($config);

        return $this->getAdapter($client, $config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config): object
    {
        return new Vfs();
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
    protected function getConfig(array $config): array
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(object $client, array $config): object
    {
        return new VfsAdapter($client);
    }
}
