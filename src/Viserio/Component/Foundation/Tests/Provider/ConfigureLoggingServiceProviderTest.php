<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Mockery as Mock;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogHandler;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Provider\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Logger;

class ConfigureLoggingServiceProviderTest extends MockeryTestCase
{
    public function testGetServicesWithSingle(): void
    {
        $container = new Container();

        $writer = $this->mock(Logger::class);
        $writer->shouldReceive('useFiles')
            ->once();

        $container->instance(Logger::class, $writer);

        $config = new Repository();
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
            public function bootstrap(): void
            {
            }
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        self::assertInstanceOf(Logger::class, $container->get(Logger::class));
    }

    public function testGetServicesWithDaily(): void
    {
        $container = new Container();

        $writer = $this->mock(Logger::class);
        $writer->shouldReceive('useDailyFiles')
            ->once();

        $container->instance(Logger::class, $writer);

        $config = new Repository();
        $config->set('viserio.app.log.handler', 'daily');
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
            public function bootstrap(): void
            {
            }
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        self::assertInstanceOf(Logger::class, $container->get(Logger::class));
    }

    public function testGetServicesWithErrorlog(): void
    {
        $container = new Container();

        $writer  = $this->mock(Logger::class);
        $handler = $this->mock(HandlerParser::class);
        $handler->shouldReceive('parseHandler')
            ->once()
            ->with(
                Mock::type(ErrorLogHandler::class),
                '',
                '',
                null,
                'line'
            );

        $container->instance(Logger::class, $writer);
        $container->instance(HandlerParser::class, $handler);

        $config = new Repository();
        $config->set('viserio.app.log.handler', 'errorlog');
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
            public function bootstrap(): void
            {
            }
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        self::assertInstanceOf(Logger::class, $container->get(Logger::class));
    }

    public function testGetServicesWithSyslog(): void
    {
        $container = new Container();

        $writer  = $this->mock(Logger::class);
        $handler = $this->mock(HandlerParser::class);
        $handler->shouldReceive('parseHandler')
            ->once()
            ->with(
                Mock::type(SyslogHandler::class),
                '',
                '',
                null,
                'line'
            );

        $container->instance(Logger::class, $writer);
        $container->instance(HandlerParser::class, $handler);

        $config = new Repository();
        $config->set('viserio.app.log.handler', 'syslog');
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
            public function bootstrap(): void
            {
            }
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        self::assertInstanceOf(Logger::class, $container->get(Logger::class));
    }
}
