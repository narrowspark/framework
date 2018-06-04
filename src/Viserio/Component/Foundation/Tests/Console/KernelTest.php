<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console;

use Closure;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contract\Console\Terminable as TerminableContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Cron\Provider\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
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

        $this->arrangeBaseServiceProvider($container);

        $this->arrangeNeverCallConsoleHandler($container);

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testHandleWithException(): void
    {
        $container = $this->mock(ContainerContract::class);

        $this->arrangeBaseServiceProvider($container);

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);
        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testTerminate(): void
    {
        $argv      = new ArgvInput();
        $container = $this->mock(ContainerContract::class);

        $container->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $container->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));
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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);

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

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $this->arrangeBootstrapManager($container);

        $kernel = $this->getKernel($container);

        $kernel->registerCommand($command);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     *
     * @return void
     */
    protected function arrangeBaseServiceProvider($container): void
    {
        $container->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $container->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     *
     * @return \Viserio\Component\Foundation\Console\Kernel
     */
    private function getKernel($container)
    {
        $kernel                      = new class($container) extends Kernel {
            private $testContainer;

            protected $bootstrappers = [];

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
            protected function registerBaseServiceProviders(): void
            {
            }

            /**
             * {@inheritdoc}
             */
            protected function registerBaseBindings(): void
            {
            }
        };

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'app' => [
                    'env'   => 'dev',
                    'debug' => true,
                ],
            ]);
        $container->shouldReceive('has')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $kernel->setKernelConfigurations($container);

        return $kernel;
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeNeverCallConsoleHandler($container): void
    {
        $handler = $this->mock(ConsoleHandlerContract::class);
        $handler->shouldReceive('report')
            ->never();

        $container->shouldReceive('get')
            ->never()
            ->with(ConsoleHandlerContract::class)
            ->andReturn($handler);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     */
    private function arrangeBootstrapManager($container): void
    {
        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $bootstrapManager->shouldReceive('addBeforeBootstrapping')
            ->twice()
            ->with(ConfigureKernel::class, \Mockery::type(Closure::class));
        $bootstrapManager->shouldReceive('addAfterBootstrapping')
            ->once()
            ->with(LoadServiceProvider::class, \Mockery::type(Closure::class));

        $container->shouldReceive('has')
            ->once()
            ->with(ServerRequestFactoryInterface::class)
            ->andReturn(true);

        $bootstrapManager->shouldReceive('addAfterBootstrapping')
            ->once()
            ->with(LoadServiceProvider::class, \Mockery::type(Closure::class));

        $container->shouldReceive('get')
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);
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
