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
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;

class StaticalProxyTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function setUp()
    {
        parent::setUp();

        StaticalProxy::clearResolvedInstances();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->andReturn(new StdClass());
        FacadeStub::setContainer($container);
    }

    public function testSwap()
    {
        ProxyStub::swap(new FacadeStub());
        self::assertEquals(new FacadeStub(), ProxyStub::getResolvedInstance()['baz']);

        ProxyStub::clearResolvedInstance('baz');
        self::assertTrue(empty(ProxyStub::getResolvedInstance()['baz']));
    }

    public function testgetInstance()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('baz')->andReturn(new StdClass());
        ProxyStub::setContainer($container);

        self::assertEquals(new StdClass(), ProxyStub::getInstance());
    }

    public function testCallStatic()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new ProxyStub());
        FacadeStub::setContainer($container);

        self::assertEquals(1, FacadeStub::oneArg(1));
        self::assertEquals(2, FacadeStub::twoArg(1, 1));
        self::assertEquals(3, FacadeStub::threeArg(1, 1, 1));
        self::assertEquals(4, FacadeStub::fourArg(1, 1, 1, 1));
        self::assertEquals(5, FacadeStub::moreArg(1, 1, 1, 1, 1));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A statical proxy root has not been set.
     */
    public function testCallStaticToThrowException()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new ProxyStub());
        ExceptionFacadeStub::setContainer($container);

        self::assertEquals(1, ExceptionFacadeStub::arg(1));
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
        self::assertEquals(new StdClass(), FacadeObjectStub::getStaticalProxyRoot());
    }

    public function testFacadeCallsUnderlyingApplication()
    {
        $container = $this->mock(ContainerInterface::class);
        $mock = Mock::mock('StdClass');
        $mock->shouldReceive('bar')->once()->andReturn('baz');
        $container->shouldReceive('get')->once()->andReturn($mock);

        FacadeStub::setContainer($container);
        self::assertEquals('baz', FacadeStub::bar());
    }

    public function testShouldReceiveReturnsAMockeryMock()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new StdClass());

        FacadeStub::setContainer($container);

        self::assertInstanceOf(
            'Mockery\MockInterface',
            FacadeStub::shouldReceive('foo')->with('bar')->andReturn('baz')->getMock()
        );
    }

    public function testShouldReceiveCanBeCalledTwice()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new StdClass());

        FacadeStub::setContainer($container);

        self::assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo')->with('bar')->andReturn('baz')->getMock()
        );
        self::assertInstanceOf(
            'Mockery\MockInterface',
            $mock = FacadeStub::shouldReceive('foo2')->with('bar2')->andReturn('baz2')->getMock()
        );
    }

    public function testCanBeMockedWithoutUnderlyingInstance()
    {
        FacadeStub::shouldReceive('foo')->andReturn('bar');

        self::assertEquals('bar', FacadeStub::foo());
    }
}
