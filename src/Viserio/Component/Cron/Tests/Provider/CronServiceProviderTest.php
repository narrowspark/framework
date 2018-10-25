<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Provider\CacheServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Component\Cron\Provider\CronServiceProvider;
use Viserio\Component\Cron\Schedule;

/**
 * @internal
 */
final class CronServiceProviderTest extends TestCase
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
                    'console' => 'cerebro',
                    'path'    => \dirname(__DIR__),
                ],
            ],
        ]);

        static::assertInstanceOf(ScheduleContract::class, $container->get(ScheduleContract::class));
        static::assertInstanceOf(ScheduleContract::class, $container->get(Schedule::class));
    }
}
