<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Adapters;

use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem;

class VfsConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config)
    {
        return new FileSystem();
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
    protected function getAdapter($client, array $config): \League\Flysystem\AdapterInterface
    {
        return new VfsAdapter($client);
    }
}
