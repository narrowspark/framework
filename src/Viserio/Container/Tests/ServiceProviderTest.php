<?php
declare(strict_types=1);
namespace Viserio\Container\Tests;

use Viserio\Container\Container;
use Viserio\Container\Tests\Fixture\ServiceFixture;
use Viserio\Container\Tests\Fixture\SimpleFixtureServiceProvider;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        self::assertEquals('value', $container->get('param'));
        self::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testProviderWithRegisterMethod()
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        self::assertEquals('value', $container->get('param'));
        self::assertEquals('anotherValue', $container->get('anotherParameter'));
        self::assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testExtendingValue()
    {
        $container = new Container();
        $container->instance('previous', 'foo');
        $container->register(new SimpleFixtureServiceProvider());

        $getPrevious = $container->get('previous');

        self::assertEquals('foo', $getPrevious());
    }

    public function testExtendingNothing()
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        self::assertNull($container->get('previous'));
    }
}
