<?php
declare(strict_types=1);
namespace Viserio\Log\Tests\Providers;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Log\Log;
use Viserio\Log\Providers\LoggerServiceProvider;
use Viserio\Log\Writer as MonologWriter;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Events\Providers\EventsServiceProvider;

class LoggerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new LoggerServiceProvider());

        $container->get(ConfigManager::class)->set('logger', [
            'env' => 'dev',
        ]);

        $this->assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get('logger'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new LoggerServiceProvider());

        $container->instance('options', [
            'env' => 'dev',
        ]);

        $this->assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get('logger'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new LoggerServiceProvider());
        $container->register(new EventsServiceProvider());

        $container->instance('viserio.log.options', [
            'env' => 'dev',
        ]);

        $this->assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        $this->assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        $this->assertInstanceOf(DispatcherContract::class, $container->get('logger')->getEventsDispatcher());
    }
}
