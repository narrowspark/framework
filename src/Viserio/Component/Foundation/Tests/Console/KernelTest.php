<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Viserio\Component\Console\Application as Cerebro;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Cron\Providers\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;
use Viserio\Component\Foundation\Console\Kernel;

class KernelTest extends MockeryTestCase
{
    public function testHandle()
    {
        $app = $this->mock(ApplicationContract::class);

        $this->getBootstrap($app);
        $app->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $app->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));

        $handler = $this->mock(HandlerContract::class);
        $handler->shouldReceive('report')
            ->never();

        $app->shouldReceive('get')
            ->never()
            ->with(HandlerContract::class)
            ->andReturn($handler);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('run')
            ->once()
            ->andReturn(0);
        $cerebro->shouldReceive('renderException')
            ->never();

        $app->shouldReceive('make')
            ->never();
        $app->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = new Kernel($app);
        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testHandleWithException()
    {
        $app = $this->mock(ApplicationContract::class);

        $this->getBootstrap($app);
        $app->shouldReceive('register')
            ->once()
            ->with(CronServiceProvider::class);
        $app->shouldReceive('get')
            ->once()
            ->with(Schedule::class)
            ->andReturn($this->mock(Schedule::class));

        $handler = $this->mock(HandlerContract::class);
        $handler->shouldReceive('report')
            ->once();

        $app->shouldReceive('get')
            ->once()
            ->with(HandlerContract::class)
            ->andReturn($handler);
        $app->shouldReceive('make')
            ->never();

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->once();

        $app->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = new Kernel($app);
        $kernel->handle(new ArgvInput(), new ConsoleOutput());
    }

    public function testTerminate()
    {
        $app = $this->mock(ApplicationContract::class);

        $handler = $this->mock(HandlerContract::class);
        $handler->shouldReceive('unregister')
            ->once();

        $app->shouldReceive('get')
            ->once()
            ->with(HandlerContract::class)
            ->andReturn($handler);

        $kernel = new Kernel($app);
        $kernel->terminate(new ArgvInput(), 0);
    }

    public function testGetAll()
    {
        $app = $this->mock(ApplicationContract::class);

        $this->getBootstrap($app);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('all')
            ->once()
            ->andReturn([]);

        $app->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = new Kernel($app);

        self::assertTrue(is_array($kernel->getAll()));
    }

    public function testGetOutput()
    {
        $app = $this->mock(ApplicationContract::class);

        $this->getBootstrap($app);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('output')
            ->once()
            ->andReturn('test');

        $app->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = new Kernel($app);

        self::assertSame('test', $kernel->getOutput());
    }

    public function testCall()
    {
        $app = $this->mock(ApplicationContract::class);

        $this->getBootstrap($app);

        $cerebro = $this->mock(Cerebro::class);
        $cerebro->shouldReceive('add')
            ->never();
        $cerebro->shouldReceive('renderException')
            ->never();
        $cerebro->shouldReceive('output')
            ->once()
            ->andReturn('test');

        $app->shouldReceive('get')
            ->once()
            ->with(Cerebro::class)
            ->andReturn($cerebro);

        $kernel = new Kernel($app);

        self::assertSame('test', $kernel->getOutput());
    }

    private function getBootstrap($app)
    {
        $app->shouldReceive('hasBeenBootstrapped')
            ->once()
            ->andReturn(false);
        $app->shouldReceive('bootstrapWith')
            ->once()
            ->with([
                LoadConfiguration::class,
                LoadEnvironmentVariables::class,
                HandleExceptions::class,
                LoadServiceProvider::class,
                SetRequestForConsole::class,
            ]);
    }
}
