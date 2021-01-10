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
use ReflectionException;
use Viserio\Component\Container\ClassHelper;
use Viserio\Component\Container\Tests\Fixture\Autowire\OptionalClass;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\ClassHelper
 *
 * @small
 */
final class ClassHelperTest extends TestCase
{
    public function testIsClassLoaded(): void
    {
        self::assertTrue(ClassHelper::isClassLoaded(EmptyClass::class));
    }

    public function testIsClassLoadedWithNotFoundClass(): void
    {
        self::assertFalse(ClassHelper::isClassLoaded(Undefined::class));
    }

    public function testBadParentWithNoTimestamp(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class Viserio\Component\Container\Tests\Fixture\Autowire\NotExistClass not found');

        ClassHelper::isClassLoaded(OptionalClass::class);
    }
}
