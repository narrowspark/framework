<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSetCommand;

class DispatcherTest extends MockeryTestCase
{
    public function testBasicDispatchingOfCommandsToHandlers(): void
    {
        $container = new ArrayContainer();
        $handler   = new class() {
            public function handle()
            {
                return 'foo';
            }
        };

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

    public function testDispatchShouldCallAfterResolvingIfCommand(): void
    {
        $container = new ArrayContainer();
        $handler   = new class() {
            public function handle()
            {
                return 'foo';
            }

            public function after()
            {
                return true;
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->mapUsing(function () {
            return 'Handler@handle';
        });

        $dispatcher->dispatch(new BusDispatcherBasicCommand(), function ($handler): void {
            self::assertTrue($handler->after());
        });
    }

    public function testDispatcherShouldNotCallHanlde(): void
    {
        $container = new ArrayContainer();
        $handler   = new class() {
            public function test()
            {
                return 'foo';
            }
        };

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

    public function testResolveHandler(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        self::assertInstanceOf(
            BusDispatcherSetCommand::class,
            $dispatcher->resolveHandler(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerClass(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        self::assertSame(
            BusDispatcherSetCommand::class,
            $dispatcher->getHandlerClass(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerMethod(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer());

        self::assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherSetCommand()));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No handler registered for command [Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand].
     */
    public function testToThrowInvalidArgumentException(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer());
        $dispatcher->via('test');

        self::assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherBasicCommand()));
    }

    public function testPipeThrough(): void
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

    public function testMaps(): void
    {
        $container = new ArrayContainer();
        $handler   = new class() {
            public function handle()
            {
                return 'foo';
            }

            public function test()
            {
                return 'bar';
            }
        };

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
