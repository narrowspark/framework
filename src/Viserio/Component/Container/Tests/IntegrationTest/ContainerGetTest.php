<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\Alias\AliasEnvFixture;
use Viserio\Component\Container\Tests\Fixture\Alias\SomeAliasFactory;
use Viserio\Component\Container\Tests\Fixture\ContainerClassWithInterfaceOptionalParameter;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Fixture\InvokeCallableTestClass;
use Viserio\Component\Container\Tests\Fixture\InvokeCallableWithConstructorParameterTestClass;
use Viserio\Component\Container\Tests\Fixture\OptionalParameterFollowedByRequiredParameter;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Container\Exception\NotFoundException;

/**
 * @internal
 */
final class ContainerGetTest extends BaseContainerTest
{
    public function testGetToThrowExceptionOnNoStringType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $id parameter must be of type string, [stdClass] given.');

        $this->assertFalse($this->compiledContainerBuilder->get(new stdClass()));
    }

    public function testGetToThrowExceptionOnNotFoundId(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Abstract [test] is not being managed by the container.');

        $this->assertFalse($this->compiledContainerBuilder->get('test'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testContainerBuilderCanCompileAInvokableClass(ContainerBuilder $builder): void
    {
        $builder->instance('foo', [InvokeCallableTestClass::class, '__invoke']);

        $container = $builder->build();

        $this->assertEquals(42, $container->get('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testContainerBuilderCanCompileAInvokableClassWithConstructorParameter(
        ContainerBuilder $builder
    ): void {
        $builder->bind(InvokeCallableWithConstructorParameterTestClass::class);
        $builder->instance('foo', [InvokeCallableWithConstructorParameterTestClass::class, '__invoke']);

        $container = $builder->build();

        $this->assertInstanceOf(FactoryClass::class, $container->get('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testInterfaceOptionalParameter(ContainerBuilder $builder): void
    {
        $builder->singleton(ContainerClassWithInterfaceOptionalParameter::class);

        $container = $builder->build();

        $this->assertInstanceOf(ContainerClassWithInterfaceOptionalParameter::class, $container->get(ContainerClassWithInterfaceOptionalParameter::class));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testOptionalParameterFollowedByRequiredParameters(ContainerBuilder $builder): void
    {
        $builder->singleton(OptionalParameterFollowedByRequiredParameter::class);

        $container = $builder->build();

        $object = $container->get(OptionalParameterFollowedByRequiredParameter::class);

        $this->assertNull($object->first);
        $this->assertInstanceOf(\stdClass::class, $object->second);
    }


    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testGetWithAliasedClass(ContainerBuilder $builder): void
    {

        $builder->instance(AliasEnvFixture::class, function () {
            return new AliasEnvFixture();
        });
        $builder->bind(SomeAliasFactory::class);

        $container = $builder->build();

        $someAliasFactory = $container->get(SomeAliasFactory::class);

        $this->assertInstanceOf(AliasEnvFixture::class, $someAliasFactory->alias);
    }
}
