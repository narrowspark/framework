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

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\ZipConnector;

/**
 * @internal
 *
 * @small
 */
final class ZipConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new ZipConnector(['path' => __DIR__ . '\stubs\test.zip']);

        $return = $connector->connect();

        self::assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new ZipConnector(['path' => __DIR__ . '\stubs\test.zip', 'prefix' => 'your-prefix']);

        $return = $connector->connect();

        self::assertInstanceOf(ZipArchiveAdapter::class, $return);
    }
}
