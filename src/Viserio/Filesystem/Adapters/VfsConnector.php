<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem;
use Narrowspark\Arr\StaticArr as Arr;

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
