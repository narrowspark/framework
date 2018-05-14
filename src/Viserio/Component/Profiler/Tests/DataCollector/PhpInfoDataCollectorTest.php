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

namespace Viserio\Component\Profiler\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;

/**
 * @internal
 *
 * @small
 */
final class PhpInfoDataCollectorTest extends MockeryTestCase
{
    public function testCollect(): void
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            \Mockery::mock(ServerRequestInterface::class),
            \Mockery::mock(ResponseInterface::class)
        );

        self::assertRegExp('~^' . \preg_quote($collect->getPhpVersion(), '~') . '~', \PHP_VERSION);
        self::assertRegExp('~' . \preg_quote((string) $collect->getPhpVersionExtra(), '~') . '$~', \PHP_VERSION);
        self::assertSame(\PHP_INT_SIZE * 8, $collect->getPhpArchitecture());
        self::assertSame(\date_default_timezone_get(), $collect->getPhpTimezone());
    }

    public function testGetMenu(): void
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            \Mockery::mock(ServerRequestInterface::class),
            \Mockery::mock(ResponseInterface::class)
        );

        self::assertSame(
            [
                'label' => 'PHP Version',
                'value' => \PHP_VERSION,
            ],
            $collect->getMenu()
        );
    }
}
