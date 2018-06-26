<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Provider\LoggerServiceProvider;

/**
 * @internal
 */
final class LoggerServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->instance('config', [
            'viserio' => [
                'logging' => [
                    'path' => '',
                    'name' => '',
                ],
            ],
        ]);

        static::assertInstanceOf(LogManager::class, $container->get(LogManager::class));
        static::assertInstanceOf(LogManager::class, $container->get('log'));
    }
}
