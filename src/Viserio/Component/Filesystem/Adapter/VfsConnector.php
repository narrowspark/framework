<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem as Vfs;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;

final class VfsConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        return new VfsAdapter(new Vfs());
    }
}
