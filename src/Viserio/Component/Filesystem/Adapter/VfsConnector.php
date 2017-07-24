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
    protected function getClient(array $config): object
    {
        return new Vfs();
    }

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
     */
    protected function getAdapter(object $client, array $config): object
    {
        return new VfsAdapter($client);
    }
}
