<?php
declare(strict_types=1);
namespace Viserio\Log\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Log\Providers\LogsDataCollectorServiceProvider;
use Viserio\Log\DataCollectors\LogParser;
use Viserio\Log\DataCollectors\LogsDataCollector;

class LogsDataCollectorServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new LogsDataCollectorServiceProvider());

        self::assertInstanceOf(LogParser::class, $container->get(LogParser::class));
        self::assertInstanceOf(LogsDataCollector::class, $container->get(LogsDataCollector::class));
    }
}
