<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\Tests\Fixture\ServiceFixture;
use Viserio\Component\Container\Tests\Fixture\SimpleFixtureServiceProvider;

/**
 * @internal
 */
final class ServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        static::assertEquals('value', $container->get('param'));
        static::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testProviderWithRegisterMethod(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        static::assertEquals('value', $container->get('param'));
        static::assertEquals('anotherValue', $container->get('anotherParameter'));
        static::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testExtendingValue(): void
    {
        $container = new Container();
        $container->instance('previous', 'foo');
        $container->register(new SimpleFixtureServiceProvider());

        static::assertEquals('foofoo', $container->get('previous'));
    }

    public function testExtendingNothing(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        static::assertSame('', $container->get('previous'));
    }
}
