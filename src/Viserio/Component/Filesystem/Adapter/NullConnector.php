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

use League\Flysystem\Adapter\NullAdapter;
use League\Flysystem\AdapterInterface;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;

final class NullConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        return new NullAdapter();
    }
}
