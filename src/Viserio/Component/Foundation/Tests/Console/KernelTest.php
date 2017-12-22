<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contract\Console\Terminable as TerminableContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Cron\Provider\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Component\Foundation\Console\Kernel;

class KernelTest extends MockeryTestCase
{
    public function testIfClassHasConsoleAndTerminableContract(): void
    {
        $interfaces = \class_implements(new Kernel());

        self::assertTrue(isset($interfaces[TerminableContract::class]));
        self::assertTrue(isset($interfaces[ConsoleKernelContract::class]));
    }

    public function testConsoleHandle(): void
    {
        $container = $this->mock(ContainerContract::class);

        $container->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $container->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));

        $handler = $this->mock(ExceptionHandlerContract::class);
        $handler->shouldReceive('report')
            ->never();

        $container->shouldReceive('get')
            ->never()
            ->with(ExceptionHandlerContract::class)
            ->andReturn($handler);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $this->getBootstrap($container);

        $kernel = $this->getKernel($container);

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testHandleWithException(): void
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);

        $container->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $container->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));

        $handler = $this->mock(ExceptionHandlerContract::class);
        $handler->shouldReceive('report')
            ->once();
        $handler->shouldReceive('renderForConsole')
            ->once();

        $container->shouldReceive('get')
            ->twice()
            ->with(ExceptionHandlerContract::class)
            ->andReturn($handler);
        $container->shouldReceive('resolve')
            ->never();

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $kernel = $this->getKernel($container);
        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testTerminate(): void
    {
        $argv      = new ArgvInput();
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);

        $container->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $container->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));
        $cerebro = $this->mock(Cerebro::class);

        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->times(3)
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $kernel = $this->getKernel($container);

        $kernel->terminate(new ArgvInput(), 0);

        $kernel->handle($argv, new ConsoleOutput());

        $kernel->terminate($argv, 0);
    }

    public function testGetAll(): void
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('all')
            ->once()
            ->andReturn([]);

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($this->mock(KernelContract::class));

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $kernel = $this->getKernel($container);

        self::assertTrue(\is_array($kernel->getAll()));
    }

    public function testGetOutput(): void
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('output')
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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $kernel = $this->getKernel($container);

        self::assertSame('test', $kernel->getOutput());
    }

    public function testCommandCall(): void
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $kernel = $this->getKernel($container);

        self::assertSame(0, $kernel->call('foo'));
    }

    public function testCommand(): void
    {
        $container = $this->mock(ContainerContract::class);
        $function  = function () {
            return 'true';
        };
        $command = new ClosureCommand('foo', $function);

        $kernel = $this->getKernel($container);

        self::assertEquals($command, $kernel->command('foo', $function));
    }

    public function testRegisterCommand(): void
    {
        $container = $this->mock(ContainerContract::class);

        $command = new ClosureCommand('foo', function () {
            return 'true';
        });

        $this->getBootstrap($container);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('setVersion')
            ->once()
            ->with(AbstractKernel::VERSION);
        $cerebro->shouldReceive('setName')
            ->once()
            ->with('Cerebro');
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

        $bootstrapManager = $this->mock(new BootstrapManager($container));

        $container->shouldReceive('get')
            ->once()
            ->with(BootstrapManager::class)
            ->andReturn($bootstrapManager);

        $kernel = $this->getKernel($container);

        $kernel->registerCommand($command);
    }

    private function getBootstrap($container): void
    {
        $setRequestForConsole = $this->mock(SetRequestForConsole::class);
        $setRequestForConsole->shouldReceive('bootstrap')
            ->once();

        $container->shouldReceive('resolve')
            ->once()
            ->with(SetRequestForConsole::class)
            ->andReturn($setRequestForConsole);
    }

    private function getKernel($container)
    {
        $kernel                      = new class($container) extends Kernel {
            protected $bootstrappers = [
                SetRequestForConsole::class,
            ];

            public function __construct($container)
            {
                $this->container = $container;

                parent::__construct();
            }

            /**
             * {@inheritdoc}
             */
            protected function initializeContainer(): void
            {
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
                    'env' => 'dev',
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
}
