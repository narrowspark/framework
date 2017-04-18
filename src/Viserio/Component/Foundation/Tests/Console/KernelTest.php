<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console;

use Closure;
use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Contracts\Console\Terminable as TerminableContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Cron\Providers\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;
use Viserio\Component\Foundation\Console\Kernel;
use Viserio\Component\Foundation\EnvironmentDetector;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;

class KernelTest extends MockeryTestCase
{
    public function testIfClassHasConsoleAndTerminableContracts()
    {
        $interfaces = class_implements(new Kernel());

        self::assertTrue(isset($interfaces[TerminableContract::class]));
        self::assertTrue(isset($interfaces[ConsoleKernelContract::class]));
    }

    public function testConsoleHandle()
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
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('run')
            ->once()
            ->andReturn(0);
        $cerebro->shouldReceive('renderException')
            ->never();

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

        $kernel = $this->getKernel($container);

        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testHandleWithException()
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

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
        $cerebro->shouldReceive('add')
            ->never();

        $container->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = $this->getKernel($container);
        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testTerminate()
    {
        $container = $this->mock(ContainerContract::class);

        $kernel = $this->getKernel($container);
        $kernel->terminate(new ArgvInput(), 0);

        $container->shouldReceive('singleton')
            ->once()
            ->with(EnvironmentContract::class, EnvironmentDetector::class);
        $container->shouldReceive('singleton')
            ->once()
            ->with(KernelContract::class, Mock::type(Closure::class));
        $container->shouldReceive('alias')
            ->once()
            ->with(KernelContract::class, 'kernel');
        $container->shouldReceive('singleton')
            ->once()
            ->with(ConsoleKernelContract::class, Mock::type(Closure::class));
        $container->shouldReceive('alias')
            ->once()
            ->with(KernelContract::class, AbstractKernel::class);
        $container->shouldReceive('alias')
            ->once()
            ->with(ConsoleKernelContract::class, 'console_kernel');
        $container->shouldReceive('alias')
            ->once()
            ->with(ConsoleKernelContract::class, Kernel::class);
        $container->shouldReceive('alias')
            ->once()
            ->with(Cerebro::class, Kernel::class);

        $this->registerBaseProvider($container);

        $kernel = $this->getKernel($container);
        $kernel->boot();
        $kernel->terminate(new ArgvInput(), 0);
    }

    public function testGetAll()
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

        $cerebro = $this->mock(Cerebro::class);
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

        $kernel = $this->getKernel($container);

        self::assertTrue(is_array($kernel->getAll()));
    }

    public function testGetOutput()
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

        $cerebro = $this->mock(Cerebro::class);
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

        $kernel = $this->getKernel($container);

        self::assertSame('test', $kernel->getOutput());
    }

    public function testCommandCall()
    {
        $container = $this->mock(ContainerContract::class);

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

        $cerebro = $this->mock(Cerebro::class);
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

        self::assertSame(0, $kernel->call('foo'));
    }

    public function testCommand()
    {
        $container = $this->mock(ContainerContract::class);
        $function  = function () {
            return 'true';
        };
        $command  = new ClosureCommand('foo', $function);

        $kernel = $this->getKernel($container);

        self::assertEquals($command, $kernel->command('foo', $function));
    }

    public function testRegisterCommand()
    {
        $container = $this->mock(ContainerContract::class);

        $command  = new ClosureCommand('foo', function () {
            return 'true';
        });

        $this->getBootstrap($container);
        $this->registerBaseProvider($container);

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

    private function getBootstrap($container)
    {
        $setRequestForConsole = $this->mock(SetRequestForConsole::class);
        $setRequestForConsole->shouldReceive('bootstrap')
            ->once();

        $container->shouldReceive('resolve')
            ->once()
            ->with(SetRequestForConsole::class)
            ->andReturn($setRequestForConsole);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappedEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappingEvent::class));

        $container->shouldReceive('get')
            ->once()
            ->with(EventManagerContract::class)
            ->andReturn($events);
        $container->shouldReceive('singleton')
            ->once()
            ->with(EnvironmentContract::class, EnvironmentDetector::class);
        $container->shouldReceive('singleton')
            ->once()
            ->with(KernelContract::class, Mock::type(Closure::class));
        $container->shouldReceive('alias')
            ->once()
            ->with(KernelContract::class, 'kernel');
        $container->shouldReceive('singleton')
            ->once()
            ->with(ConsoleKernelContract::class, Mock::type(Closure::class));
        $container->shouldReceive('alias')
            ->once()
            ->with(KernelContract::class, AbstractKernel::class);
        $container->shouldReceive('alias')
            ->once()
            ->with(ConsoleKernelContract::class, 'console_kernel');
        $container->shouldReceive('alias')
            ->once()
            ->with(ConsoleKernelContract::class, Kernel::class);
        $container->shouldReceive('alias')
            ->once()
            ->with(Cerebro::class, Kernel::class);
    }

    private function registerBaseProvider($container)
    {
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(EventsServiceProvider::class));
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(OptionsResolverServiceProvider::class));
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(RoutingServiceProvider::class));
        $container->shouldReceive('register')
            ->once()
            ->with(Mock::type(ConsoleServiceProvider::class));
    }

    private function getKernel($container)
    {
        return new class($container) extends Kernel {
            protected $bootstrappers = [
                SetRequestForConsole::class,
            ];

            public function __construct($container)
            {
                $this->container = $container;
            }

            protected function initializeContainer(): void
            {
            }
        };
    }
}
