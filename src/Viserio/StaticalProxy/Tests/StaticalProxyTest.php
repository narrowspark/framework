<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests;

use Mockery as Mock;
use StdClass;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\StaticalProxy\Tests\Fixture\ExceptionFacadeStub;
use Viserio\StaticalProxy\Tests\Fixture\FacadeObjectStub;
use Viserio\StaticalProxy\Tests\Fixture\FacadeStub;
use Viserio\StaticalProxy\Tests\Fixture\ProxyStub;

class StaticalProxyTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        StaticalProxy::clearResolvedInstances();

        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->shouldReceive('get')->andReturn(new StdClass());
        FacadeStub::setContainer($container);
    }

    public function tearDown()
    {
        Mock::close();
    }

    public function testSwap()
    {
        ProxyStub::swap(new FacadeStub());
        $this->assertEquals(new FacadeStub(), ProxyStub::getResolvedInstance()['baz']);

        ProxyStub::clearResolvedInstance('baz');
        $this->assertTrue(empty(ProxyStub::getResolvedInstance()['baz']));
    }

    public function testgetInstance()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->shouldReceive('get')->with('baz')->andReturn(new StdClass());
        ProxyStub::setContainer($container);

        $this->assertEquals(new StdClass(), ProxyStub::getInstance());
    }

    public function testCallStatic()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->shouldReceive('get')->with('foo')->andReturn(new ProxyStub());
        FacadeStub::setContainer($container);

        $this->assertEquals(1, FacadeStub::oneArg(1));
        $this->assertEquals(2, FacadeStub::twoArg(1, 1));
        $this->assertEquals(3, FacadeStub::threeArg(1, 1, 1));
        $this->assertEquals(4, FacadeStub::fourArg(1, 1, 1, 1));
        $this->assertEquals(5, FacadeStub::moreArg(1, 1, 1, 1, 1));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A statical proxy root has not been set.
     */
    public function testCallStaticToThrowException()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->shouldReceive('get')->with('foo')->andReturn(new ProxyStub());
        ExceptionFacadeStub::setContainer($container);

        $this->assertEquals(1, ExceptionFacadeStub::arg(1));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage The Viserio\StaticalProxy\StaticalProxy::getInstanceIdentifier method must be implemented by a subclass.
     */
    public function testGetInstanceIdentifier()
    {
        ExceptionFacadeStub::getInstanceIdentifier();
    }

    public function testGetStaticalProxyRoot()
    {
        $this->assertEquals(new StdClass(), FacadeObjectStub::getStaticalProxyRoot());
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
        $container->shouldReceive('get')->with('foo')->andReturn(new StdClass());

        FacadeStub::setContainer($container);

        $this->assertInstanceOf(
            'Mockery\MockInterface',
            FacadeStub::shouldReceive('foo')->with('bar')->andReturn('baz')->getMock()
        );
    }

    public function testShouldReceiveCanBeCalledTwice()
    {
        $container = Mock::mock('Interop\Container\ContainerInterface');
        $container->shouldReceive('get')->with('foo')->andReturn(new StdClass());

        FacadeStub::setContainer($container);

        $this->assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo')->with('bar')->andReturn('baz')->getMock()
        );
        $this->assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo2')->with('bar2')->andReturn('baz2')->getMock()
        );
    }

    public function testCanBeMockedWithoutUnderlyingInstance()
    {
        FacadeStub::shouldReceive('foo')->andReturn('bar');

        $this->assertEquals('bar', FacadeStub::foo());
    }
}
