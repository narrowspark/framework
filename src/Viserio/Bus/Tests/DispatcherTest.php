<?php
namespace Viserio\Bus\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Narrowspark\TestingHelper\ArrayContainer;
use stdClass;
use Viserio\Bus\Dispatcher;
use Viserio\Bus\Tests\Fixture\{
    BusDispatcherBasicCommand,
    BusDispatcherSetCommand
};

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testBasicDispatchingOfCommandsToHandlers()
    {
        $container = new ArrayContainer();
        $handler = $this->mock(stdClass::class);
        $handler->shouldReceive('handle')
            ->once()
            ->andReturn('foo');

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->mapUsing(function () {
            return 'Handler@handle';
        });

        $this->assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testDispatchShouldCallAfterResolvingIfCommand()
    {
        $container = new ArrayContainer();
        $handler = $this->mock(stdClass::class)->shouldIgnoreMissing();
        $handler->shouldReceive('after')
            ->once();

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->mapUsing(function () {
            return 'Handler@handle';
        });

        $dispatcher->dispatch(new BusDispatcherBasicCommand(), function ($handler) {
            $handler->after();
        });
    }

    public function testDispatcherShouldNotCallHanlde()
    {
        $container = new ArrayContainer();
        $handler = $this->mock(stdClass::class);
        $handler->shouldReceive('test')->once()->andReturn('foo');

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->via('test')->mapUsing(function () {
            return 'Handler@test';
        });

        $this->assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testResolveHandler()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        $this->assertInstanceOf(
            BusDispatcherSetCommand::class,
            $dispatcher->resolveHandler(new BusDispatcherSetCommand())
        );
    }


    public function testGetHandlerClass()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        $this->assertSame(
            BusDispatcherSetCommand::class,
            $dispatcher->getHandlerClass(new BusDispatcherSetCommand())
        );
    }


    public function testGetHandlerMethod()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        $this->assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherSetCommand()));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage No handler registered for command [Viserio\Bus\Tests\Fixture\BusDispatcherBasicCommand].
     */
    public function testToThrowInvalidArgumentException()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());
        $dispatcher->via('test');

        $this->assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherBasicCommand()));
    }

    public function testPipeThrough()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());
        $dispatcher->pipeThrough([
            function ($piped, $next) {
                $piped = $piped->set('test');

                return $next($piped);
            }
        ]);

        $this->assertEquals(
            'test',
            $dispatcher->dispatch(new BusDispatcherSetCommand())
        );
    }

    public function testMaps()
    {
        $container = new ArrayContainer();
        $handler = $this->mock(stdClass::class);
        $handler->shouldReceive('handle')->andReturn('foo');
        $handler->shouldReceive('test')->andReturn('bar');

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->maps([
            BusDispatcherBasicCommand::class => 'Handler@handle',
            BusDispatcherBasicCommand::class => 'Handler@test',
        ]);

        $this->assertEquals(
            'bar',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }
}
