<?php
declare(strict_types=1);
namespace Viserio\Log\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Log\DataCollectors\LogParser;
use Viserio\Log\DataCollectors\LogsDataCollector;
use Viserio\Log\Providers\LogsDataCollectorServiceProvider;

class LogsDataCollectorServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new LogsDataCollectorServiceProvider());

        self::assertInstanceOf(LogParser::class, $container->get(LogParser::class));
        self::assertInstanceOf(LogsDataCollector::class, $container->get(LogsDataCollector::class));
    }

    public function testProviderWithConfigManager()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new LogsDataCollectorServiceProvider());

        $container->get(RepositoryContract::class)
            ->set('path.storage', __DIR__);

        self::assertInstanceOf(LogParser::class, $container->get(LogParser::class));
        self::assertInstanceOf(LogsDataCollector::class, $container->get(LogsDataCollector::class));
    }
}
