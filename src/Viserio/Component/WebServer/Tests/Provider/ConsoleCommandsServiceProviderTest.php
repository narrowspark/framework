<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\WebServer\Command\ServerDumpCommand;
use Viserio\Component\WebServer\Command\ServerLogCommand;
use Viserio\Component\WebServer\Command\ServerServeCommand;
use Viserio\Component\WebServer\Command\ServerStartCommand;
use Viserio\Component\WebServer\Command\ServerStatusCommand;
use Viserio\Component\WebServer\Command\ServerStopCommand;
use Viserio\Component\WebServer\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\WebServer\Provider\WebServerServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends MockeryTestCase
{
    public function testProvider(): void
    {
        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getRootDir')
            ->once()
            ->andReturn(__DIR__);
        $kernel->shouldReceive('getEnvironment')
            ->once()
            ->andReturn('env');

        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new WebServerServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());
        $container->instance(ConsoleKernelContract::class, $kernel);
        $container->instance(ServerRequestInterface::class, new ServerRequest('/'));
        $container->instance('config', ['viserio' => []]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        static::assertInstanceOf(ServerDumpCommand::class, $commands['server:dump']);
        static::assertInstanceOf(ServerLogCommand::class, $commands['server:log']);
        static::assertInstanceOf(ServerServeCommand::class, $commands['server:serve']);
        static::assertInstanceOf(ServerStartCommand::class, $commands['server:start']);
        static::assertInstanceOf(ServerStatusCommand::class, $commands['server:status']);
        static::assertInstanceOf(ServerStopCommand::class, $commands['server:stop']);
    }

    public function testGetDimensions(): void
    {
        static::assertSame(['viserio'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        static::assertSame(
            [
                'console' => [
                    'lazily_commands' => [
                        'server:serve'    => ServerServeCommand::class,
                        'server:start'    => ServerStartCommand::class,
                        'server:stop'     => ServerStopCommand::class,
                        'server:status'   => ServerStatusCommand::class,
                        'server:dump'     => ServerDumpCommand::class,
                        'server:log'      => ServerLogCommand::class,
                    ],
                ],
                'webserver' => [
                    'web_folder' => 'public',
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
