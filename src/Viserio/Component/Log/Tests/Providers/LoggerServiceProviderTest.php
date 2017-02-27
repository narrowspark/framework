<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Providers;

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Log\Log;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Providers\LoggerServiceProvider;
use Viserio\Component\Log\Writer as MonologWriter;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class LoggerServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
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
