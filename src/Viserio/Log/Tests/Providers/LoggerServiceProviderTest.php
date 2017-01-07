<?php
declare(strict_types=1);
namespace Viserio\Log\Tests\Providers;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\Log\Log;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Log\Providers\LoggerServiceProvider;
use Viserio\Log\Writer as MonologWriter;

class LoggerServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new LoggerServiceProvider());

        $container->get(RepositoryContract::class)->set('logger', [
            'env' => 'dev',
        ]);

        self::assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        self::assertInstanceOf(MonologWriter::class, $container->get('logger'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new LoggerServiceProvider());

        $container->instance('options', [
            'env' => 'dev',
        ]);

        self::assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        self::assertInstanceOf(MonologWriter::class, $container->get('logger'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new LoggerServiceProvider());
        $container->register(new EventsServiceProvider());

        $container->instance('viserio.log.options', [
            'env' => 'dev',
        ]);

        self::assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        self::assertInstanceOf(EventManagerContract::class, $container->get('logger')->getEventManager());
    }
}
