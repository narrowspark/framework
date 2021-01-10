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

namespace Viserio\Component\Container\Tests\Unit\PhpParser\Reflection;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\PhpParser\Reflection\PrivatesCaller;
use Viserio\Component\Container\Tests\Fixture\Reflection\SomeClassWithPrivateMethods;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PhpParser\Reflection\PrivatesCaller
 *
 * @small
 */
final class PrivatesCallerTest extends TestCase
{
    public function testCallPrivateMethod(): void
    {
        self::assertSame(5, PrivatesCaller::callPrivateMethod(
            SomeClassWithPrivateMethods::class,
            'getNumber'
        ));
        self::assertSame(5, PrivatesCaller::callPrivateMethod(
            new SomeClassWithPrivateMethods(),
            'getNumber'
        ));
        self::assertSame(40, PrivatesCaller::callPrivateMethod(
            new SomeClassWithPrivateMethods(),
            'plus10',
            30
        ));
    }

    public function testCallPrivateMethodWithReference(): void
    {
        self::assertSame(20, PrivatesCaller::callPrivateMethodWithReference(
            new SomeClassWithPrivateMethods(),
            'multipleByTwo',
            10
        ));
    }
}
