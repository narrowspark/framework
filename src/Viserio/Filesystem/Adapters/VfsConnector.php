<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Vfs\VfsAdapter;
use Narrowspark\Arr\StaticArr as Arr;
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

    protected function getAuth(array $config)
    {
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config)
    {
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config)
    {
        return new VfsAdapter($client);
    }
}
