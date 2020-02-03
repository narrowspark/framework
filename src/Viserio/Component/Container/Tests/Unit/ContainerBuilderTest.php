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
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Tests\Fixture\Autowire\OptionalClass;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Contract\Container\Exception\BindingResolutionException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\ContainerBuilder
 *
 * @small
 */
final class ContainerBuilderTest extends TestCase
{
    /** @var \Viserio\Component\Container\ContainerBuilder */
    private $containerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * @param mixed $class
     * @param mixed $expected
     *
     * @dataProvider provideGetClassReflectorCases
     */
    public function testGetClassReflector($class, $expected): void
    {
        $reflection = $this->containerBuilder->getClassReflector($class);

        self::assertInstanceOf(ReflectionClass::class, $reflection);
        self::assertSame($expected, $reflection->getName());
    }

    public static function provideGetClassReflectorCases(): iterable
    {
        return [
            [EmptyClass::class, EmptyClass::class],
            ['Viserio\Component\Container\Tests\Fixture\EmptyClass', EmptyClass::class],
        ];
    }

    public function testGetClassReflectorThrowsBindingResolutionExceptionOnNotFoundClass(): void
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class Viserio\Component\Container\Tests\Fixture\Autowire\NotExistClass not found');

        $this->containerBuilder->getClassReflector(OptionalClass::class);
    }

    /**
     * @param mixed $method
     * @param mixed $expectedClass
     * @param mixed $expectedMethod
     *
     * @dataProvider provideGetMethodReflectorCases
     */
    public function testGetMethodReflector($method, $expectedClass, $expectedMethod): void
    {
        [$class, $m] = FactoryDefinition::splitFactory($method);

        $reflection = $this->containerBuilder->getMethodReflector(new ReflectionClass($class), $m);

        self::assertInstanceOf(ReflectionMethod::class, $reflection);
        self::assertSame($expectedClass, $reflection->getDeclaringClass()->getName());
        self::assertSame($expectedMethod, $reflection->getName());
    }

    public static function provideGetMethodReflectorCases(): iterable
    {
        return [
            [FactoryClass::class . '@create', FactoryClass::class, 'create'],
            [[FactoryClass::class, 'create'], FactoryClass::class, 'create'],
            ['Viserio\Component\Container\Tests\Fixture\FactoryClass@create', FactoryClass::class, 'create'],
            [['Viserio\Component\Container\Tests\Fixture\FactoryClass', 'create'], FactoryClass::class, 'create'],
            [[new FactoryClass(), 'create'], FactoryClass::class, 'create'],
            [FactoryClass::class . '::staticCreate', FactoryClass::class, 'staticCreate'],
            [[FactoryClass::class, 'staticCreate'], FactoryClass::class, 'staticCreate'],
            ['Viserio\Component\Container\Tests\Fixture\FactoryClass@staticCreate', FactoryClass::class, 'staticCreate'],
            [['Viserio\Component\Container\Tests\Fixture\FactoryClass', 'staticCreate'], FactoryClass::class, 'staticCreate'],
            [[new FactoryClass(), 'staticCreate'], FactoryClass::class, 'staticCreate'],
        ];
    }

    public function testGetMethodReflectorThrowsExceptionIfMethodIsNotFoundInClass(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Unable to reflect on method [test], the method does not exist in class [Viserio\Component\Container\Tests\Fixture\EmptyClass].');

        $this->containerBuilder->getMethodReflector(new ReflectionClass(EmptyClass::class), 'test');
    }

    /**
     * @param mixed $function
     * @param mixed $expectedFunction
     * @param mixed $expectedReflectionClass
     *
     * @dataProvider provideGetFunctionReflectorCases
     */
    public function testGetFunctionReflector($function, $expectedFunction, $expectedReflectionClass): void
    {
        $reflection = $this->containerBuilder->getFunctionReflector($function);

        self::assertInstanceOf($expectedReflectionClass, $reflection);
        self::assertSame($expectedFunction, $reflection->getName());
    }

    public static function provideGetFunctionReflectorCases(): iterable
    {
        return [
            ['time', 'time', ReflectionFunction::class],
            ['is_method', 'is_method', ReflectionFunction::class],
            [
                function () {
                    return 'test';
                },
                'Viserio\Component\Container\Tests\Unit\{closure}',
                ReflectionFunction::class,
            ],
        ];
    }

    public function testGetFunctionReflectorThrows(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Function test() does not exist');

        $this->containerBuilder->getFunctionReflector('test');
    }
}
