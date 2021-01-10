<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Tests\Integration\Pipeline;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\UnusedTagsPipe;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Pipeline\UnusedTagsPipe
 *
 * @small
 */
final class UnusedTagsPipeTest extends TestCase
{
    public function testProcess(): void
    {
        $pass = new UnusedTagsPipe([
            'console',
        ]);

        $container = new ContainerBuilder();

        $container->bind('foo', stdClass::class)
            ->addTag('cnosole');
        $container->bind('bar', stdClass::class)
            ->addTag('cnosole');

        $pass->process($container);

        self::assertSame([\sprintf('%s: Tag [cnosole] was defined on services ["foo", "bar"], but was never used. Did you mean [console]?', UnusedTagsPipe::class)], $container->getLogs());
    }
}
