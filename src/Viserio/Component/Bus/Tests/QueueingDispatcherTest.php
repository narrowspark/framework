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

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Assert;
use RuntimeException;
use stdClass;
use Viserio\Component\Bus\QueueingDispatcher;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherBasicCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherCustomQueueCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherQueuedHandler;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSpecificDelayCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSpecificQueueAndDelayCommand;
use Viserio\Component\Bus\Tests\Fixture\BusDispatcherSpecificQueueCommand;
use Viserio\Contract\Queue\QueueConnector as QueueConnectorContract;
use Viserio\Contract\Queue\ShouldQueue as ShouldQueueContract;

/**
 * @internal
 *
 * @small
 */
final class QueueingDispatcherTest extends MockeryTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::markTestSkipped('Queue needs to be refactored.');
    }

    public function testDispatchNowShouldNeverQueue(): void
    {
        $container = new ArrayContainer([]);
        $handler = new class() {
            public function handle(): string
            {
                return 'foo';
            }
        };

        $container->set('Handler', $handler);

        $dispatcher = new QueueingDispatcher($container);
        $dispatcher->mapUsing(static function () {
            return 'Handler@handle';
        });

        self::assertEquals(
            'foo',
            $dispatcher->dispatch(Mock::mock(ShouldQueueContract::class))
        );
    }

    public function testHandlersThatShouldQueueIsQueued(): void
    {
        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            $mock = Mock::mock(QueueConnectorContract::class);
            $mock->shouldReceive('push')
                ->once();

            return $mock;
        });

        $dispatcher->mapUsing(static function () {
            return BusDispatcherQueuedHandler::class . '@handle';
        });

        $dispatcher->dispatch(new BusDispatcherBasicCommand());
    }

    public function testCommandsThatShouldQueueIsQueuedUsingCustomQueueAndDelay(): void
    {
        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            $mock = Mock::mock(QueueConnectorContract::class);
            $mock->shouldReceive('laterOn')
                ->once()
                ->with('foo', 10, Mock::type(BusDispatcherSpecificQueueAndDelayCommand::class));

            return $mock;
        });

        $dispatcher->dispatch(new BusDispatcherSpecificQueueAndDelayCommand());
    }

    public function testCommandsThatShouldQueueIsQueuedUsingCustomQueue(): void
    {
        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            $mock = Mock::mock(QueueConnectorContract::class);
            $mock->shouldReceive('pushOn')
                ->once()
                ->with('foo', Mock::type(BusDispatcherSpecificQueueCommand::class));

            return $mock;
        });

        $dispatcher->dispatch(new BusDispatcherSpecificQueueCommand());
    }

    public function testCommandsThatShouldQueueIsQueuedUsingCustomDelay(): void
    {
        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            $mock = Mock::mock(QueueConnectorContract::class);
            $mock->shouldReceive('later')
                ->once()
                ->with(10, Mock::type(BusDispatcherSpecificDelayCommand::class));

            return $mock;
        });

        $dispatcher->dispatch(new BusDispatcherSpecificDelayCommand());
    }

    public function testCommandsThatShouldQueueIsQueued(): void
    {
        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            $mock = Mock::mock(QueueConnectorContract::class);
            $mock->shouldReceive('push')->once();

            return $mock;
        });

        $dispatcher->dispatch(Mock::mock(ShouldQueueContract::class));
    }

    public function testDispatchShouldCallAfterResolvingIfCommandNotQueued(): void
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

        $dispatcher = new QueueingDispatcher($container);
        $dispatcher->mapUsing(static function () {
            return 'Handler@handle';
        });

        $dispatcher->dispatch(new BusDispatcherBasicCommand(), function ($handler): void {
            Assert::assertTrue($handler->after());
        });
    }

    public function testCommandsThatShouldQueueIsQueuedUsingCustomHandler(): void
    {
        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            $mock = Mock::mock(QueueConnectorContract::class);
            $mock->shouldReceive('push')->once();

            return $mock;
        });

        $dispatcher->dispatch(new BusDispatcherCustomQueueCommand());
    }

    public function testCommandsThatShouldThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Queue resolver did not return a Queue implementation.');

        $container = new ArrayContainer([]);

        $dispatcher = new QueueingDispatcher($container, function () {
            return Mock::mock(stdClass::class);
        });

        $dispatcher->dispatch(new BusDispatcherCustomQueueCommand());
    }
}
