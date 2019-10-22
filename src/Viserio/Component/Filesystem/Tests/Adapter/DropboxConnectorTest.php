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

use PHPUnit\Framework\TestCase;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Viserio\Component\Filesystem\Adapter\DropboxConnector;

/**
 * @internal
 *
 * @small
 */
final class DropboxConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new DropboxConnector([
            'token' => 'your-token',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new DropboxConnector([
            'token' => 'your-token',
            'prefix' => 'your-prefix',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }
}
