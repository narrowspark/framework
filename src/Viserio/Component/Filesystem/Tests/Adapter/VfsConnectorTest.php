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

namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Vfs\VfsAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\VfsConnector;

/**
 * @internal
 *
 * @small
 */
final class VfsConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new VfsConnector();

        $return = $connector->connect();

        self::assertInstanceOf(VfsAdapter::class, $return);
    }
}
