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

namespace Viserio\Component\Container\Tests\UnitTest;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\RewindableGenerator;
use function count;

/**
 * @internal
 *
 * @small
 */
final class RewindableGeneratorTest extends TestCase
{
    public function testCountUsesProvidedValue(): void
    {
        $generator = new RewindableGenerator(function () {
            yield 'foo';
        }, 999);

        self::assertCount(999, $generator);
    }

    public function testCountUsesProvidedValueAsCallback(): void
    {
        /** @var int $called */
        $called = 0;

        $generator = new RewindableGenerator(function () {
            yield 'foo';
        }, function () use (&$called) {
            $called++;

            return 500;
        });

        // the count callback is called lazily
        self::assertSame(0, $called);
        self::assertCount(500, $generator);

        \count($generator);

        // the count callback is called only once
        self::assertEquals(1, $called);
    }
}
