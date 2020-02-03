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
