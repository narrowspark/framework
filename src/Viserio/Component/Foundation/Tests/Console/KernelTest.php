<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Foundation\Tests\Console;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Foundation\Console\Kernel;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Contract\Console\Terminable as TerminableContract;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;

/**
 * @internal
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @small
 * @coversNothing
 */
final class KernelTest extends MockeryTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['APP_ENV'] = 'prod';
        $_SERVER['APP_DEBUG'] = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // remove env
        \putenv('APP_ENV=');
        \putenv('APP_ENV');
        \putenv('APP_DEBUG=');
        \putenv('APP_DEBUG');
    }

    public function testIfClassHasConsoleAndTerminableContract(): void
    {
        $interfaces = \class_implements(new Kernel());

        self::assertTrue(isset($interfaces[TerminableContract::class]));
        self::assertTrue(isset($interfaces[ConsoleKernelContract::class]));
    }

    public function testConsoleHandle(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);

        $handler = Mockery::mock(ConsoleHandlerContract::class);
        $handler->shouldReceive('report')
            ->never();

        $container->shouldReceive('has')
            ->never()
            ->with(ConsoleHandlerContract::class)
            ->andReturnTrue();

        $container->shouldReceive('get')
            ->never()
            ->with(ConsoleHandlerContract::class)
            ->andReturn($handler);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('run')
            ->once()
            ->andReturn(0);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testHandleWithException(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);
        $container->shouldReceive('has')
            ->twice()
            ->with(ConsoleHandlerContract::class)
            ->andReturnTrue();

        $handler = Mockery::mock(ConsoleHandlerContract::class);
        $handler->shouldReceive('report')
            ->once();
        $handler->shouldReceive('render')
            ->once();

        $container->shouldReceive('get')
            ->twice()
            ->with(ConsoleHandlerContract::class)
            ->andReturn($handler);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testTerminate(): void
    {
        $argv = new ArgvInput();
        $container = Mockery::mock(CompiledContainerContract::class);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('run')
            ->once()
            ->andReturn(0);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $kernel->terminate(new ArgvInput(), 0);

        $kernel->handle($argv, new ConsoleOutput());

        $kernel->terminate($argv, 0);
    }

    public function testGetAll(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('all')
            ->twice()
            ->andReturn([]);

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        self::assertIsArray($kernel->getAll());
        // testing cache of getConsole
        self::assertIsArray($kernel->getAll());
    }

    public function testGetOutput(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('getLastOutput')
            ->once()
            ->andReturn('test');

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        self::assertSame('test', $kernel->getOutput());
    }

    public function testCommandCall(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('call')
            ->once()
            ->with('foo')
            ->andReturn(0);

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        self::assertSame(0, $kernel->call('foo'));
    }

    public function testRegisterCommand(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);

        $command = new ClosureCommand('foo', static function () {
            return 'true';
        });

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->once()
            ->with($command);

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $kernel->registerCommand($command);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Contract\Container\CompiledContainer $container
     */
    private function getKernel($container): Kernel
    {
        $kernel = new class($container) extends Kernel {
            public function __construct($container)
            {
                $this->container = $container;

                parent::__construct();
            }

            /**
             * {@inheritdoc}
             */
            public function getRootDir(): string
            {
                return $this->rootDir = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';
            }

            /**
             * {@inheritdoc}
             */
            protected function registerBaseBindings(): void
            {
            }
        };

        return $kernel;
    }
}
