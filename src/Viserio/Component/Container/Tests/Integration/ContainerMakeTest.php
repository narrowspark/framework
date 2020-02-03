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

namespace Viserio\Component\Container\Tests\Integration;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Container\AbstractCompiledContainer;
use Viserio\Component\Container\Tests\Fixture\Autowire\C;
use Viserio\Component\Container\Tests\Fixture\Autowire\PrivateConstructor;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Fixture\FooClass;
use Viserio\Component\Container\Tests\Fixture\Make\ContractFixtureInterface;
use Viserio\Component\Container\Tests\Fixture\Make\DefaultValue;
use Viserio\Component\Container\Tests\Fixture\Make\DependentFixture;
use Viserio\Component\Container\Tests\Fixture\Make\ImplementationFixture;
use Viserio\Component\Container\Tests\Fixture\Make\MixedPrimitiveFixture;
use Viserio\Component\Container\Tests\Fixture\Make\NestedDependentFixture;
use Viserio\Contract\Container\Exception\BindingResolutionException;
use Viserio\Contract\Container\Exception\UnresolvableDependencyException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\ContainerBuilder
 *
 * @small
 */
final class ContainerMakeTest extends MockeryTestCase
{
    /** @var AbstractCompiledContainer */
    protected $abstractContainer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractContainer = new class() extends AbstractCompiledContainer {
        };
    }

    public function testMakeMethod(): void
    {
        self::assertSame(
            'Hello',
            $this->abstractContainer->make(FactoryClass::class . '@create')
        );
    }

    public function testMakeClosureResolution(): void
    {
        self::assertSame('Narrowspark', $this->abstractContainer->make(function () {
            return 'Narrowspark';
        }));
    }

    public function testMakeClosureResolutionWithUse(): void
    {
        $class = new stdClass();

        self::assertSame(
            $class,
            $this->abstractContainer->make(function () use ($class) {
                return $class;
            })
        );
    }

    public function testMakeResolutionWithClass(): void
    {
        self::assertEquals(
            new FactoryClass(),
            $this->abstractContainer->make(FactoryClass::class)
        );

        self::assertEquals(
            new stdClass(),
            $this->abstractContainer->make(new stdClass())
        );

        $abstractContainer = new class() extends AbstractCompiledContainer {
            protected array $aliases = [
                'alias' => FactoryClass::class,
            ];
        };

        self::assertEquals(new FactoryClass(), $abstractContainer->make('alias'));

        $object = $this->abstractContainer->make(FooClass::class, [['foo' => 'test']]);

        self::assertSame(['foo' => 'test'], $object->arguments);

        $object = $this->abstractContainer->make(new FooClass(), [['foo' => 'test']]);

        self::assertSame(['foo' => 'test'], $object->arguments);
    }

    public function testMakeCanResolveCallback(): void
    {
        $value = $this->abstractContainer->make(
            function (ContainerInterface $container, $test) {
                return $test;
            },
            [$this->abstractContainer, 'test']
        );

        self::assertSame('test', $value);
    }

    public function testSharedConcreteResolution(): void
    {
        $var1 = $this->abstractContainer->make(EmptyClass::class);
        $var2 = $this->abstractContainer->make(EmptyClass::class);

        self::assertSame($var1, $var2);
    }

    public function testParametersCanOverrideDependencies(): void
    {
        $mock = Mockery::mock(ContractFixtureInterface::class);
        $stub = new DependentFixture($mock);
        $resolved = $this->abstractContainer->make(NestedDependentFixture::class, [$stub]);

        self::assertInstanceOf(NestedDependentFixture::class, $resolved);
        self::assertEquals($mock, $resolved->inner->impl);
    }

    public function testAbstractToConcreteResolution(): void
    {
        $abstractContainer = new class() extends AbstractCompiledContainer {
            protected array $methodMapping = [
                ContractFixtureInterface::class => 'getBar',
            ];

            public function getBar(): ImplementationFixture
            {
                return new ImplementationFixture();
            }
        };

        self::assertInstanceOf(ImplementationFixture::class, $abstractContainer->make(DependentFixture::class)->impl);
    }

    public function testNestedDependencyResolution(): void
    {
        $abstractContainer = new class() extends AbstractCompiledContainer {
            protected array $methodMapping = [
                ContractFixtureInterface::class => 'getBar',
            ];

            public function getBar(): ImplementationFixture
            {
                return new ImplementationFixture();
            }
        };

        $class = $abstractContainer->make(NestedDependentFixture::class);

        self::assertInstanceOf(DependentFixture::class, $class->inner);
        self::assertInstanceOf(ImplementationFixture::class, $class->inner->impl);
    }

    public function testMakeWithAliases(): void
    {
        $abstractContainer = new class() extends AbstractCompiledContainer {
            protected array $aliases = [
                'foo' => stdClass::class,
                'bat' => stdClass::class,
            ];
        };

        self::assertInstanceOf(stdClass::class, $abstractContainer->make(stdClass::class));
        self::assertInstanceOf(stdClass::class, $abstractContainer->make('foo'));
        self::assertInstanceOf(stdClass::class, $abstractContainer->make('bat'));
    }

    public function testParametersCanBePassedThroughToClosure(): void
    {
        self::assertEquals([1, 2, 3], $this->abstractContainer->make(function (ContainerInterface $container, $a, $b, $c) {
            return [$a, $b, $c];
        }, [$this->abstractContainer, 1, 2, 3]));
    }

    public function testResolutionOfDefaultParameters(): void
    {
        $instance = $this->abstractContainer->make(DefaultValue::class);

        self::assertInstanceOf(ImplementationFixture::class, $instance->stub);
        self::assertEquals('narrowspark', $instance->default);
    }

    public function testUnableToReflectClass(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('The class [Viserio\\Component\\Container\\Tests\\Fixture\\Autowire\\PrivateConstructor] is not instantiable.');

        $this->abstractContainer->make(PrivateConstructor::class);
    }

    public function testBindingResolutionExceptionMessage(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('The interface [Viserio\\Component\\Container\\Tests\\Fixture\\Make\\ContractFixtureInterface] is not instantiable.');

        $this->abstractContainer->make(ContractFixtureInterface::class);
    }

    public function testBindingResolutionExceptionMessageIncludesBuildStack(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('The interface [Viserio\\Component\\Container\\Tests\\Fixture\\Autowire\\AInterface] is not instantiable. Build stack: [Viserio\\Component\\Container\\Tests\\Fixture\\Autowire\\C].');

        $this->abstractContainer->make(C::class);
    }

    public function testInternalClassWithDefaultParameters(): void
    {
        $this->expectException(UnresolvableDependencyException::class);
        $this->expectExceptionMessage('Argument [$first] of method [Viserio\\Component\\Container\\Tests\\Fixture\\Make\\MixedPrimitiveFixture::__construct] has no type-hint, you should configure its value explicitly.');

        $this->abstractContainer->make(MixedPrimitiveFixture::class);
    }

    public function testMakeWithUnResolvable(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('[string] is not resolvable. Build stack : []');

        $this->abstractContainer->make('foo');
    }

    public function testMakeWithContainerInterface(): void
    {
        self::assertInstanceOf(ContainerInterface::class, $this->abstractContainer->make(ContainerInterface::class));
    }
}
