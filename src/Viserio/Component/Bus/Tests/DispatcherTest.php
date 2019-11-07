<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Bus\Tests;

use InvalidArgumentException;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Assert;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSetCommand;

/**
 * @internal
 *
 * @small
 */
final class DispatcherTest extends MockeryTestCase
{
    public function testBasicDispatchingOfCommandsToHandlers(): void
    {
        $container = new ArrayContainer([]);
        $handler = new class() {
            public function handle(): string
            {
                return 'foo';
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->mapUsing(static function () {
            return 'Handler@handle';
        });

        self::assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testDispatchShouldCallAfterResolvingIfCommand(): void
    {
        $container = new ArrayContainer([]);
        $handler = new class() {
            public function handle(): string
            {
                return 'foo';
            }

            public function after(): bool
            {
                return true;
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->mapUsing(static function () {
            return 'Handler@handle';
        });

        $dispatcher->dispatch(new BusDispatcherBasicCommand(), function ($handler): void {
            Assert::assertTrue($handler->after());
        });
    }

    public function testDispatcherShouldNotCallHandle(): void
    {
        $container = new ArrayContainer([]);
        $handler = new class() {
            public function batman(): string
            {
                return 'foo';
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->via('batman')->mapUsing(static function () {
            return 'Handler@batman';
        });

        self::assertEquals(
            'foo',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }

    public function testResolveHandler(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));

        self::assertInstanceOf(
            BusDispatcherSetCommand::class,
            $dispatcher->resolveHandler(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerClass(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));

        self::assertSame(
            BusDispatcherSetCommand::class,
            $dispatcher->getHandlerClass(new BusDispatcherSetCommand())
        );
    }

    public function testGetHandlerMethod(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));

        self::assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherSetCommand()));
    }

    public function testToThrowInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No handler registered for command [Viserio\\Component\\Bus\\Tests\\Fixture\\BusDispatcherBasicCommand].');

        $dispatcher = new Dispatcher(new ArrayContainer([]));
        $dispatcher->via('batman');

        self::assertSame('handle', $dispatcher->getHandlerMethod(new BusDispatcherBasicCommand()));
    }

    public function testPipeThrough(): void
    {
        $dispatcher = new Dispatcher(new ArrayContainer([]));
        $dispatcher->pipeThrough([
            static function ($piped, $next) {
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
        $container = new ArrayContainer([]);
        $handler = new class() {
            public function handle(): string
            {
                return 'foo';
            }

            public function batman(): string
            {
                return 'bar';
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new Dispatcher($container);
        $dispatcher->maps([
            BusDispatcherBasicCommand::class => 'Handler@batman',
        ]);

        self::assertEquals(
            'bar',
            $dispatcher->dispatch(new BusDispatcherBasicCommand())
        );
    }
}
