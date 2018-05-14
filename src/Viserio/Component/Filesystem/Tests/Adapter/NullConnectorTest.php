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

use League\Flysystem\Adapter\NullAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\NullConnector;

/**
 * @internal
 *
 * @small
 */
final class NullConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new NullConnector();

        $return = $connector->connect();

        self::assertInstanceOf(NullAdapter::class, $return);
    }
}
