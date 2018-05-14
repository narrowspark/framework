<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\ServiceFixture;
use Viserio\Component\Container\Tests\Fixture\SimpleFixtureServiceProvider;
use Viserio\Component\Container\Tests\Fixture\SimpleTaggedServiceProvider;

/**
 * @internal
 */
final class ContainerServiceProviderTest extends BaseContainerTest
{
    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testProvideraa(ContainerBuilder $builder): void
    {
        $builder->register(new SimpleFixtureServiceProvider());

        $container = $builder->build();

        static::assertEquals('value', $container['param']);
        static::assertInstanceOf(ServiceFixture::class, $container['service']);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testTaggedProvider(ContainerBuilder $builder): void
    {
        $builder->register(new SimpleTaggedServiceProvider());

        $container = $builder->build();

        static::assertSame('value', $container['param']);

        $array = $container->getTagged('test');

        static::assertSame('value', $array[0]);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testProviderWithRegisterMethod(ContainerBuilder $builder): void
    {
        $builder->register(new SimpleFixtureServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        $container = $builder->build();

        static::assertEquals('value', $container->get('param'));
        static::assertEquals('anotherValue', $container->get('anotherParameter'));
        static::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendingValue(ContainerBuilder $builder): void
    {
        $builder->instance('previous', 'foo');
        $builder->register(new SimpleFixtureServiceProvider());

        $container = $builder->build();

        static::assertEquals('foofoo', $container->get('previous'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testExtendingNothing(ContainerBuilder $builder): void
    {
        $builder->register(new SimpleFixtureServiceProvider());

        $container = $builder->build();

        static::assertSame('', $container->get('previous'));
    }
}
