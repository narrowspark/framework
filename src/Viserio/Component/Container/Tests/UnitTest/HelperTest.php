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
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeCallableClass;
use Viserio\Component\Container\Tests\Fixture\Method\ClassWithMethods;

/**
 * @internal
 *
 * @small
 */
final class HelperTest extends TestCase
{
    public function testIsClass(): void
    {
        self::assertTrue(is_class('Acme\UnknownClass'));
        self::assertTrue(is_class(EmptyClass::class));
        self::assertTrue(is_class(\stdClass::class));
        self::assertTrue(is_class('\DateTime'));

        self::assertFalse(is_class('foo'));
    }

    public function testIsInvokable(): void
    {
        self::assertTrue(is_invokable(InvokeCallableClass::class));
        self::assertTrue(is_invokable(new InvokeCallableClass()));

        self::assertFalse(is_invokable(EmptyClass::class));
    }

    public function testIsMethod(): void
    {
        self::assertTrue(is_method([ClassWithMethods::class, 'foo']));
        self::assertTrue(is_method([new ClassWithMethods(), 'foo']));
        self::assertTrue(is_method(ClassWithMethods::class . '@foo'));

        self::assertFalse(is_method([EmptyClass::class, 'foo']));
        self::assertFalse(is_method(EmptyClass::class . '@foo'));
        self::assertFalse(is_method([EmptyClass::class]));
    }

    public function testIsStaticMethod(): void
    {
        self::assertTrue(is_static_method(ClassWithMethods::class . '::bar'));

        self::assertFalse(is_static_method([ClassWithMethods::class, 'foo']));
        self::assertFalse(is_static_method(EmptyClass::class . '::foo'));
    }

    public function testIsFunction(): void
    {
        self::assertTrue(is_function(function (): void {
        }));
        self::assertTrue(is_function('is_string'));
        self::assertFalse(is_function('foo'));
    }
}
