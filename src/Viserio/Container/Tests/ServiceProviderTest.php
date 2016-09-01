<?php
declare(strict_types=1);
namespace Viserio\Container\Tests;

use Viserio\Container\Container;
use Viserio\Container\Tests\Fixture\SimpleFixtureServiceProvider;
use Viserio\Container\Tests\Fixture\ServiceFixture;

class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        $this->assertEquals('value', $container->get('param'));
        $this->assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testProviderWithRegisterMethod()
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider(), array(
            'anotherParameter' => 'anotherValue',
        ));

        $this->assertEquals('value', $container->get('param'));
        $this->assertEquals('anotherValue', $container->get('anotherParameter'));
        $this->assertInstanceOf(ServiceFixture::class, $container->get('service'));
    }

    public function testExtendingValue()
    {
        $container = new Container();
        $container->instance('previous', 'foo');
        $container->register(new SimpleFixtureServiceProvider());

        $getPrevious = $container->get('previous');

        $this->assertEquals('foo', $getPrevious());
    }

    public function testExtendingNothing()
    {
        $container = new Container();
        $container->register(new SimpleFixtureServiceProvider());

        $this->assertNull($container->get('previous'));
    }
}
