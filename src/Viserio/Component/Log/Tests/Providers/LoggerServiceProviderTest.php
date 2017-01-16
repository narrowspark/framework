<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Providers;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Log\Log;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Log\Providers\LoggerServiceProvider;
use Viserio\Component\Log\Writer as MonologWriter;

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
