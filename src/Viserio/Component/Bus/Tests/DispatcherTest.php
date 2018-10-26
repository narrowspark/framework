<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSetCommand;

/**
 * @internal
 */
final class DispatcherTest extends MockeryTestCase
{
    public function testBasicDispatchingOfCommandsToHandlers(): void
    {
        $container = new ArrayContainer([]);
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

        $this->assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testDispatchShouldCallAfterResolvingIfCommand(): void
    {
        $container = new ArrayContainer([]);
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
            $this->assertTrue($handler->after());
        });
    }

    public function testDispatcherShouldNotCallHandle(): void
    {
        $container = new ArrayContainer([]);
        $handler   = new class() {
            public function batman()
            {
                return 'foo';
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->via('batman')->mapUsing(function () {
            return 'Handler@batman';
        });

        $this->assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testResolveHandler(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));

        $this->assertInstanceOf(
            BusDispatcherSetCommand::class,
            $dispatcher->resolveHandler(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerClass(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));

        $this->assertSame(
            BusDispatcherSetCommand::class,
            $dispatcher->getHandlerClass(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerMethod(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));

        $this->assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherSetCommand()));
    }

    public function testToThrowInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No handler registered for command [Viserio\\Component\\Bus\\Tests\\Fixture\\BusDispatcherBasicCommand].');

        $dispatcher = new Dispatcher(new ArrayContainer([]));
        $dispatcher->via('batman');

        $this->assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherBasicCommand()));
    }

    public function testPipeThrough(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));
        $dispatcher->pipeThrough([
            function ($piped, $next) {
                $piped = $piped->set('test');

                return $next($piped);
            },
        ]);

        $this->assertEquals(
            'test',
            $dispatcher->dispatch(new BusDispatcherSetCommand())
        );
    }

    public function testMaps(): void
    {
        $container = new ArrayContainer([]);
        $handler   = new class() {
            public function handle()
            {
                return 'foo';
            }

            public function batman()
            {
                return 'bar';
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->maps([
            BusDispatcherBasicCommand::class => 'Handler@batman',
        ]);

        $this->assertEquals(
            'bar',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }
}
