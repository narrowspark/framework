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

namespace Viserio\Component\Container\Tests\Unit\Definition;

use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Tests\Fixture\Autowire\CannotBeAutowired;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\ArgumentsTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\AutowireTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\ClassTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\DecoratedServiceTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\MethodCallsTestTrait;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Exception\BindingResolutionException;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\OutOfBoundsException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Definition\FactoryDefinition
 *
 * @small
 */
final class FactoryDefinitionTest extends AbstractDefinitionTest
{
    use ArgumentsTestTrait;
    use ClassTestTrait;
    use MethodCallsTestTrait;
    use DecoratedServiceTestTrait;
    use AutowireTestTrait;

    /** @var \Viserio\Component\Container\Definition\FactoryDefinition */
    protected $definition;

    public function testGetValue(): void
    {
        [$class, $method] = $this->definition->getValue();

        self::assertInstanceOf(FactoryClass::class, $class);
        self::assertSame('create', $method);
    }

    public function testSetAndGetClassArguments(): void
    {
        $this->definition->setClassArguments(['test' => new ReferenceDefinition('foo')]);

        self::assertCount(1, $this->definition->getClassArguments());
        self::assertInstanceOf(ReferenceDefinition::class, $this->definition->getClassArgument('test'));
    }

    public function testGetClassParameterThrowException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('The class parameter [0] doesn\'t exist.');

        $this->definition->getClassArgument(0);
    }

    /**
     * @dataProvider provideSplitFactoryCases
     *
     * @param array|string $value
     * @param array        $expected
     */
    public function testSplitFactory($value, array $expected): void
    {
        [$class, $method] = FactoryDefinition::splitFactory($value);
        [$expectedClass, $expectedMethod] = $expected;

        if (\is_object($class)) {
            self::assertInstanceOf($expectedClass, $class);
        } else {
            self::assertSame($expectedClass, $class);
        }

        self::assertSame($expectedMethod, $method);
    }

    public static function provideSplitFactoryCases(): iterable
    {
        return [
            [
                FactoryClass::class . '@create',
                [FactoryClass::class, 'create'],
            ],
            [
                FactoryClass::class . '::create',
                [FactoryClass::class, 'create'],
            ],
            [
                [new FactoryClass(), 'create'],
                [FactoryClass::class, 'create'],
            ],
            [
                [FactoryClass::class, 'create'],
                [FactoryClass::class, 'create'],
            ],
        ];
    }

    public function testSplitFactoryThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No method found; The $method parameter must be of type string [Class@Method or Class::Method] or array [[Class, \'Method\'] or [new Class, \'Method\']].');

        FactoryDefinition::splitFactory('');
    }

    public function testGetClass(): void
    {
        self::assertSame(FactoryClass::class, $this->definition->getClass());
    }

    public function testGetMethod(): void
    {
        self::assertSame('create', $this->definition->getMethod());
    }

    public function testConstructorThrowsException(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Invalid factory method call for [Viserio\Component\Container\Tests\Fixture\Autowire\CannotBeAutowired] : [__construct] cannot be used as a method call.');

        new FactoryDefinition('test', CannotBeAutowired::class . '::__construct', 1);
    }

    /**
     * @dataProvider provideInvalidFactoriesCases
     *
     * @param mixed $factory
     */
    public function testInvalidFactories($factory): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No method found; The $method parameter must be of type string [Class@Method or Class::Method] or array [[Class, \'Method\'] or [new Class, \'Method\']].');

        new FactoryDefinition('foo', $factory, 1);
    }

    public static function provideInvalidFactoriesCases(): iterable
    {
        return [
            [['', 'method']],
            [['class', '']],
            [['...', 'method']],
            [['class', '...']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinition(): FactoryDefinition
    {
        return new FactoryDefinition($this->getDefinitionName(), $this->value, DefinitionContract::SINGLETON);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(): array
    {
        return [new FactoryClass(), 'create'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinitionName(): string
    {
        return 'test';
    }
}
