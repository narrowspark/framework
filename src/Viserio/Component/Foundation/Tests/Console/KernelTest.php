<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contract\Console\Terminable as TerminableContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Component\Exception\Bootstrap\ConsoleHandleExceptions;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\Foundation\Console\Kernel;

/**
 * @internal
 */
final class KernelTest extends MockeryTestCase
{
    public function testIfClassHasConsoleAndTerminableContract(): void
    {
        $interfaces = \class_implements(new Kernel());

        $this->assertTrue(isset($interfaces[TerminableContract::class]));
        $this->assertTrue(isset($interfaces[ConsoleKernelContract::class]));
    }

    public function testConsoleHandle(): void
    {
        $container = $this->mock(ContainerContract::class);

        $handler = $this->mock(ConsoleHandlerContract::class);
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

        $cerebro = $this->arrangeConsoleNameAndVersion();
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

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testHandleWithException(): void
    {
        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('has')
            ->twice()
            ->with(ConsoleHandlerContract::class)
            ->andReturnTrue();

        $handler = $this->mock(ConsoleHandlerContract::class);
        $handler->shouldReceive('report')
            ->once();
        $handler->shouldReceive('render')
            ->once();

        $container->shouldReceive('get')
            ->twice()
            ->with(ConsoleHandlerContract::class)
            ->andReturn($handler);
        $container->shouldReceive('resolve')
            ->never();

        $cerebro = $this->arrangeConsoleNameAndVersion();
        $cerebro->shouldReceive('add')
            ->never();

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testTerminate(): void
    {
        $argv      = new ArgvInput();
        $container = $this->mock(ContainerContract::class);

        $cerebro = $this->arrangeConsoleNameAndVersion();
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

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $kernel->terminate(new ArgvInput(), 0);

        $kernel->handle($argv, new ConsoleOutput());

        $kernel->terminate($argv, 0);
    }

    public function testGetAll(): void
    {
        $container = $this->mock(ContainerContract::class);

        $cerebro = $this->arrangeConsoleNameAndVersion();
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

        $bootstrapManagerMock = $this->arrangeBootstrapManager($kernel);
        $bootstrapManagerMock->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);
        $bootstrapManagerMock->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(true);

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManagerMock);

        $this->assertInternalType('array', $kernel->getAll());
        // testing cache of getConsole
        $this->assertInternalType('array', $kernel->getAll());
    }

    public function testGetOutput(): void
    {
        $container = $this->mock(ContainerContract::class);

        $cerebro = $this->arrangeConsoleNameAndVersion();
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

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $this->assertSame('test', $kernel->getOutput());
    }

    public function testCommandCall(): void
    {
        $container = $this->mock(ContainerContract::class);

        $cerebro = $this->arrangeConsoleNameAndVersion();
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('call')
            ->once()
            ->with('foo', [], null)
            ->andReturn(0);

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $this->assertSame(0, $kernel->call('foo'));
    }

    public function testCommand(): void
    {
        $container = $this->mock(ContainerContract::class);
        $function  = function () {
            return 'true';
        };
        $command = new ClosureCommand('foo', $function);

        $kernel = $this->getKernel($container);

        $this->assertEquals($command, $kernel->command('foo', $function));
    }

    public function testRegisterCommand(): void
    {
        $container = $this->mock(ContainerContract::class);

        $command = new ClosureCommand('foo', function () {
            return 'true';
        });

        $cerebro = $this->arrangeConsoleNameAndVersion();
        $cerebro->shouldReceive('add')
            ->once()
            ->with($command);

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($this->arrangeBootstrapManager($kernel));

        $kernel->registerCommand($command);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     *
     * @return \Viserio\Component\Foundation\Console\Kernel
     */
    private function getKernel($container): Kernel
    {
        $kernel                      = new class($container) extends Kernel {
            private $testContainer;

            public function __construct($container)
            {
                $this->testContainer = $container;

                parent::__construct();
            }

            /**
             * {@inheritdoc}
             */
            protected function initializeContainer(): ContainerContract
            {
                return $this->testContainer;
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

        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'dev',
                    'debug' => true,
                ],
            ],
        ]);

        return $kernel;
    }

    /**
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     *
     * @return \Mockery\MockInterface|\Viserio\Component\Foundation\BootstrapManager
     */
    private function arrangeBootstrapManager($kernel)
    {
        $bootstrapManagerMock = $this->mock(new BootstrapManager($kernel));
        $bootstrapManagerMock->shouldReceive('addAfterBootstrapping')
            ->once()
            ->with(LoadServiceProvider::class, [ConsoleHandleExceptions::class, 'bootstrap']);

        $bootstrapManagerMock->shouldReceive('bootstrapWith')
            ->with([
                ConfigureKernel::class,
            ]);

        return $bootstrapManagerMock;
    }

    /**
     * @return \Mockery\MockInterface|\Viserio\Component\Console\Application
     */
    private function arrangeConsoleNameAndVersion()
    {
        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');

        return $cerebro;
    }
}
