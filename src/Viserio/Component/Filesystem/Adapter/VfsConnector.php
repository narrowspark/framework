<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Vfs\VfsAdapter;
use VirtualFileSystem\FileSystem as Vfs;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;

final class VfsConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        return new VfsAdapter(new Vfs());
    }
}
