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

namespace Viserio\Component\WebServer\Tests\Container\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\OptionsResolver\Container\Provider\OptionsResolverServiceProvider;
use Viserio\Component\WebServer\Command\ServerDumpCommand;
use Viserio\Component\WebServer\Command\ServerLogCommand;
use Viserio\Component\WebServer\Command\ServerServeCommand;
use Viserio\Component\WebServer\Command\ServerStartCommand;
use Viserio\Component\WebServer\Command\ServerStatusCommand;
use Viserio\Component\WebServer\Command\ServerStopCommand;
use Viserio\Component\WebServer\Container\Provider\WebServerServiceProvider;
use Viserio\Component\WebServer\Event\DumpListenerEvent;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Provider\Debug\Container\Provider\DebugServiceProvider;

/**
 * @internal
 *
 * @small
 */
final class WebServerServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    protected const DUMP_CLASS_CONTAINER = false;

    public function testProvider(): void
    {
        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        $this->container->set(LoggerInterface::class, \Mockery::mock(LoggerInterface::class));

        $kernel = \Mockery::mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getPublicPath')
            ->twice()
            ->andReturn(__DIR__);
        $kernel->shouldReceive('getEnvironment')
            ->twice()
            ->andReturn('env');

        $this->container->set(ConsoleKernelContract::class, $kernel);
        $this->container->set(ServerRequestInterface::class, new ServerRequest('/'));

        self::assertInstanceOf(DumpListenerEvent::class, $this->container->get(DumpListenerEvent::class));
        self::assertInstanceOf(Connection::class, $this->container->get(Connection::class));
        self::assertInstanceOf(DumpServer::class, $this->container->get(DumpServer::class));
        self::assertInstanceOf(ServerLogCommand::class, $this->container->get(ServerLogCommand::class));
        self::assertInstanceOf(ServerDumpCommand::class, $this->container->get(ServerDumpCommand::class));
        self::assertInstanceOf(ServerStatusCommand::class, $this->container->get(ServerStatusCommand::class));
        self::assertInstanceOf(ServerStopCommand::class, $this->container->get(ServerStopCommand::class));
        self::assertInstanceOf(ServerServeCommand::class, $this->container->get(ServerServeCommand::class));
        self::assertInstanceOf(ServerStartCommand::class, $this->container->get(ServerStartCommand::class));

        /** @var Application $console */
        $console = $this->container->get(Application::class);

        self::assertTrue($console->has(ServerLogCommand::getDefaultName()));
        self::assertTrue($console->has(ServerDumpCommand::getDefaultName()));
        self::assertTrue($console->has(ServerStatusCommand::getDefaultName()));
        self::assertTrue($console->has(ServerStopCommand::getDefaultName()));
        self::assertTrue($console->has(ServerServeCommand::getDefaultName()));
        self::assertTrue($console->has(ServerStartCommand::getDefaultName()));
    }

    public function testGetDimensions(): void
    {
        self::assertSame(['viserio', 'webserver'], WebServerServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        self::assertSame(
            [
                'debug_server' => [
                    'host' => 'tcp://127.0.0.1:9912',
                ],
            ],
            WebServerServiceProvider::getDefaultOptions()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', ['viserio' => []]);
        $containerBuilder->register(new ConsoleServiceProvider());
        $containerBuilder->register(new DebugServiceProvider());
        $containerBuilder->register(new OptionsResolverServiceProvider());
        $containerBuilder->register(new WebServerServiceProvider());

        $containerBuilder->singleton(ServerRequestInterface::class)
            ->setSynthetic(true);
        $containerBuilder->singleton(LoggerInterface::class)
            ->setSynthetic(true);
        $containerBuilder->singleton(ConsoleKernelContract::class)
            ->setSynthetic(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
