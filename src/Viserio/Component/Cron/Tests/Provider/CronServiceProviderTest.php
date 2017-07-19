<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Provider\CacheServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Cron\Provider\CronServiceProvider;
use Viserio\Component\Cron\Schedule;

class CronServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new CacheServiceProvider());
        $container->register(new CronServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
                'cron' => [
                    'console'    => 'cerebro',
                    'mutex_path' => __DIR__ . '/..',
                    'path'       => __DIR__ . '..',
                ],
            ],
        ]);

        self::assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }
}
