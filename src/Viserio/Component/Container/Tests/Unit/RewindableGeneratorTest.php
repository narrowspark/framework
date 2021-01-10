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

namespace Viserio\Component\Container\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\RewindableGenerator;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\RewindableGenerator
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
