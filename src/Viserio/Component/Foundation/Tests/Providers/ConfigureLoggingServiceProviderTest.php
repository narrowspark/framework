<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use Mockery as Mock;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogHandler;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Writer;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConfigureLoggingServiceProviderTest extends MockeryTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithSingle()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer = $this->mock(Writer::class);
        $writer->shouldReceive('useFiles')
            ->once();

        $container->instance(Writer::class, $writer);

        $config = new Repository();
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithDaily()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer = $this->mock(Writer::class);
        $writer->shouldReceive('useDailyFiles')
            ->once();

        $container->instance(Writer::class, $writer);

        $config = new Repository();
        $config->set('viserio.app.log.handler', 'daily');
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithErrorlog()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer  = $this->mock(Writer::class);
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

        $container->instance(Writer::class, $writer);
        $container->instance(HandlerParser::class, $handler);

        $config = new Repository();
        $config->set('viserio.app.log.handler', 'errorlog');
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetServicesWithSyslog()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        $writer  = $this->mock(Writer::class);
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

        $container->instance(Writer::class, $writer);
        $container->instance(HandlerParser::class, $handler);

        $config = new Repository();
        $config->set('viserio.app.log.handler', 'syslog');
        $config->set('viserio.app.name', 'Narrowspark');

        $container->instance(RepositoryContract::class, $config);

        $kernel = new class() extends AbstractKernel {
        };

        $container->instance(KernelContract::class, $kernel);

        $container->register(new ConfigureLoggingServiceProvider());

        static::assertInstanceOf(Writer::class, $container->get(Writer::class));
    }
}
