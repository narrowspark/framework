<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Fixture\InvokeCallableTestClass;
use Viserio\Component\Container\Tests\Fixture\InvokeCallableWithConstructorParameterTestClass;
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

        static::assertFalse($this->compiledContainerBuilder->get(new stdClass()));
    }

    public function testGetToThrowExceptionOnNotFoundId(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Abstract [test] is not being managed by the container.');

        static::assertFalse($this->compiledContainerBuilder->get('test'));
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

        static::assertEquals(42, $container->get('foo'));
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

        static::assertInstanceOf(FactoryClass::class, $container->get('foo'));
    }
}
