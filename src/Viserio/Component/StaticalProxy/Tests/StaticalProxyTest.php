<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests;

use Mockery as Mock;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\StaticalProxy\Tests\Fixture\ExceptionSaticalProxyStub;
use Viserio\Component\StaticalProxy\Tests\Fixture\FooStaticalProxyStub;
use Viserio\Component\StaticalProxy\Tests\Fixture\StaticalProxyObjectStub;
use Viserio\Component\StaticalProxy\Tests\Fixture\StaticalProxyStub;

class StaticalProxyTest extends MockeryTestCase
{
    public function setUp()
    {
        parent::setUp();

        StaticalProxy::clearResolvedInstances();

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->andReturn(new stdClass());

        FooStaticalProxyStub::setContainer($container);
    }

    public function testSwap()
    {
        StaticalProxyStub::swap(new FooStaticalProxyStub());

        self::assertEquals(new FooStaticalProxyStub(), StaticalProxyStub::getResolvedInstance()['baz']);

        StaticalProxyStub::clearResolvedInstance('baz');

        self::assertTrue(empty(StaticalProxyStub::getResolvedInstance()['baz']));
    }

    public function testGetInstance()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('baz')->andReturn(new stdClass());
        StaticalProxyStub::setContainer($container);

        self::assertEquals(new stdClass(), StaticalProxyStub::getInstance());
    }

    public function testCallStatic()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new StaticalProxyStub());

        FooStaticalProxyStub::setContainer($container);

        self::assertEquals(1, FooStaticalProxyStub::oneArg(1));
        self::assertEquals(2, FooStaticalProxyStub::twoArg(1, 1));
        self::assertEquals(3, FooStaticalProxyStub::threeArg(1, 1, 1));
        self::assertEquals(4, FooStaticalProxyStub::fourArg(1, 1, 1, 1));
        self::assertEquals(5, FooStaticalProxyStub::moreArg(1, 1, 1, 1, 1));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A statical proxy root has not been set.
     */
    public function testCallStaticToThrowException()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new StaticalProxyStub());

        ExceptionSaticalProxyStub::setContainer($container);

        self::assertEquals(1, ExceptionSaticalProxyStub::arg(1));
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage The [Viserio\Component\StaticalProxy\StaticalProxy::getInstanceIdentifier] method must be implemented by a subclass.
     */
    public function testGetInstanceIdentifier()
    {
        ExceptionSaticalProxyStub::getInstanceIdentifier();
    }

    public function testGetStaticalProxyRoot()
    {
        self::assertEquals(new stdClass(), StaticalProxyObjectStub::getStaticalProxyRoot());
    }

    public function testFacadeCallsUnderlyingApplication()
    {
        $container = $this->mock(ContainerInterface::class);
        $mock      = Mock::mock('stdClass');
        $mock->shouldReceive('bar')->once()->andReturn('baz');
        $container->shouldReceive('get')->once()->andReturn($mock);

        FooStaticalProxyStub::setContainer($container);

        self::assertEquals('baz', FooStaticalProxyStub::bar());
    }

    public function testShouldReceiveReturnsAMockeryMock()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new stdClass());

        FooStaticalProxyStub::setContainer($container);

        self::assertInstanceOf(
            MockInterface::class,
            FooStaticalProxyStub::shouldReceive('foo')->with('bar')->andReturn('baz')->getMock()
        );
    }

    public function testShouldReceiveCanBeCalledTwice()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('foo')->andReturn(new stdClass());

        FooStaticalProxyStub::setContainer($container);

        self::assertInstanceOf(
            MockInterface::class,
            $mock = FooStaticalProxyStub::shouldReceive('foo')->with('bar')->andReturn('baz')->getMock()
        );
        self::assertInstanceOf(
            MockInterface::class,
            $mock = FooStaticalProxyStub::shouldReceive('foo2')->with('bar2')->andReturn('baz2')->getMock()
        );
    }

    public function testCanBeMockedWithoutUnderlyingInstance()
    {
        FooStaticalProxyStub::shouldReceive('foo')->andReturn('bar');

        self::assertEquals('bar', FooStaticalProxyStub::foo());
    }
}
