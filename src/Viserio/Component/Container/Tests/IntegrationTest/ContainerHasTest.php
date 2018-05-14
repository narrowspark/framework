<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class ContainerHasTest extends BaseContainerTest
{
    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testHasToThrowExceptionOnNoStringType(ContainerBuilder $builder): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The $id parameter must be of type string, [stdClass] given.');

        static::assertFalse($builder->build()->has(new stdClass()));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testHasWhenSetDirectly(ContainerBuilder $builder): void
    {
        $container = $builder->build();
        $container->instance('foo', 'bar');

        static::assertTrue($container->has('foo'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testHasNot(ContainerBuilder $builder): void
    {
        static::assertFalse($builder->build()->has('wow'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testHas(ContainerBuilder $builder): void
    {
        $builder->instance('foo', 'bar');

        static::assertTrue($builder->build()->has('foo'));
    }
}
