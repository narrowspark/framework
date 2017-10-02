<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Provider;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Log\Log;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Provider\LoggerServiceProvider;
use Viserio\Component\Log\Writer as MonologWriter;

class LoggerServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());
        $container->register(new LoggerServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'log' => [
                    'env' => 'dev',
                ],
            ],
        ]);

        self::assertInstanceOf(HandlerParser::class, $container->get(HandlerParser::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(LoggerInterface::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(MonologWriter::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Logger::class));
        self::assertInstanceOf(MonologWriter::class, $container->get(Log::class));
        self::assertInstanceOf(MonologWriter::class, $container->get('logger'));
    }
}
