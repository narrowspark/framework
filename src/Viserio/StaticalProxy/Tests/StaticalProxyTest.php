<?php
namespace Viserio\StaticalProxy\Tests;

use Mockery as Mock;
use StdClass;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\StaticalProxy\Tests\Fixture\FacadeStub;

class StaticalProxyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        StaticalProxy::clearResolvedInstances();

        $container = Mock::mock('Interop\Container\ContainerInterface');
        FacadeStub::setContainer($container);
    }

    public function tearDown()
    {
        Mock::close();
    }

    public function testFacadeCallsUnderlyingApplication()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $mock = Mock::mock('StdClass');
        $mock->shouldReceive('bar')->once()->andReturn('baz');
        $container->shouldReceive('get')->once()->andReturn($mock);

        FacadeStub::setContainer($container);
        $this->assertEquals('baz', FacadeStub::bar());
    }

    public function testShouldReceiveReturnsAMockeryMock()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->setAttributes(['foo' => new StdClass()]);

        FacadeStub::setContainer($container);

        $this->assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo')->once()->with('bar')->andReturn('baz')->getMock()
        );
        $this->assertEquals('baz', $container->get('foo')->foo('bar'));
    }

    public function testShouldReceiveCanBeCalledTwice()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->setAttributes(['foo' => new StdClass()]);

        FacadeStub::setContainer($container);

        $this->assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo')->once()->with('bar')->andReturn('baz')->getMock()
        );
        $this->assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo2')->once()->with('bar2')->andReturn('baz2')->getMock()
        );
        $this->assertEquals('baz', $container->get('foo')->foo('bar'));
        $this->assertEquals('baz2', $container->get('foo')->foo2('bar2'));
    }

    public function testCanBeMockedWithoutUnderlyingInstance()
    {
        FacadeStub::shouldReceive('foo')->once()->andReturn('bar');

        $this->assertEquals('bar', FacadeStub::foo());
    }
}
