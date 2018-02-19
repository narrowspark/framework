<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Provider\LoggerServiceProvider;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;

class LoggerServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->get(RepositoryContract::class)->setArray([
            'viserio' => [
                'logging' => [
                    'path' => '',
                    'name' => '',
                ],
            ],
        ]);
        $container->register(new EventsServiceProvider());
        $container->register(new LoggerServiceProvider());

        self::assertInstanceOf(LogManager::class, $container->get(LogManager::class));
        self::assertInstanceOf(LogManager::class, $container->get('log'));
    }
}
