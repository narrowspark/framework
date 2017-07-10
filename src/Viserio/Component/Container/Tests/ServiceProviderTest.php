<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\Tests\Fixture\ServiceFixture;
use Viserio\Component\Container\Tests\Fixture\SimpleFixtureServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        self::assertEquals('value', $container->get('param'));
        self::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testProviderWithRegisterMethod(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        self::assertEquals('value', $container->get('param'));
        self::assertEquals('anotherValue', $container->get('anotherParameter'));
        self::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testExtendingValue(): void
    {
        $container = new Container();
        $container->instance('previous', 'foo');
        $container->register(new SimpleFixtureServiceProvider());

        self::assertEquals('foofoo', $container->get('previous'));
    }

    public function testExtendingNothing(): void
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        self::assertSame('', $container->get('previous'));
    }
}
