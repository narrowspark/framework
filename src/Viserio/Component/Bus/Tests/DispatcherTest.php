<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSetCommand;

class DispatcherTest extends TestCase
{
    use MockeryTrait;

    public function testBasicDispatchingOfCommandsToHandlers()
    {
        $container = new ArrayContainer();
        $handler   = $this->mock(stdClass::class);
        $handler->shouldReceive('handle')
            ->once()
            ->andReturn('foo');

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->mapUsing(function () {
            return 'Handler@handle';
        });

        self::assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testDispatchShouldCallAfterResolvingIfCommand()
    {
        $container = new ArrayContainer();
        $handler   = $this->mock(stdClass::class)->shouldIgnoreMissing();
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
        $handler   = $this->mock(stdClass::class);
        $handler->shouldReceive('test')->once()->andReturn('foo');

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->via('test')->mapUsing(function () {
            return 'Handler@test';
        });

        self::assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testResolveHandler()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        self::assertInstanceOf(
            BusDispatcherSetCommand::class,
            $dispatcher->resolveHandler(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerClass()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        self::assertSame(
            BusDispatcherSetCommand::class,
            $dispatcher->getHandlerClass(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerMethod()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        self::assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherSetCommand()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No handler registered for command [Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand].
     */
    public function testToThrowInvalidArgumentException()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());
        $dispatcher->via('test');

        self::assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherBasicCommand()));
    }

    public function testPipeThrough()
    {
        $dispatcher = new Dispatcher(new ArrayContainer());
        $dispatcher->pipeThrough([
            function ($piped, $next) {
                $piped = $piped->set('test');

                return $next($piped);
            },
        ]);

        self::assertEquals(
            'test',
            $dispatcher->dispatch(new BusDispatcherSetCommand())
        );
    }

    public function testMaps()
    {
        $container = new ArrayContainer();
        $handler   = $this->mock(stdClass::class);
        $handler->shouldReceive('handle')->andReturn('foo');
        $handler->shouldReceive('test')->andReturn('bar');

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->maps([
            BusDispatcherBasicCommand::class => 'Handler@handle',
            BusDispatcherBasicCommand::class => 'Handler@test',
        ]);

        self::assertEquals(
            'bar',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }
}
