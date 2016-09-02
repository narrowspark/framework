<?php
declare(strict_types=1);
namespace Viserio\Log\Tests\Providers;

use Defuse\Crypto\Key;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Log\Writer as MonologWriter;
use Viserio\Config\Manager as ConfigManager;
use Psr\Log\LoggerInterface;
use Viserio\Log\Providers\LoggerServiceProvider;
use Monolog\Logger;
use Viserio\Contracts\Log\Log;

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
}
