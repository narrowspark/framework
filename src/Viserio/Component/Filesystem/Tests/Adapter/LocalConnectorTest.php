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

use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\LocalConnector;

/**
 * @internal
 *
 * @small
 */
final class LocalConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new LocalConnector(['path' => __DIR__]);

        $return = $connector->connect();

        self::assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new LocalConnector(['path' => __DIR__, 'prefix' => 'your-prefix']);

        $return = $connector->connect();

        self::assertInstanceOf(Local::class, $return);
    }
}
