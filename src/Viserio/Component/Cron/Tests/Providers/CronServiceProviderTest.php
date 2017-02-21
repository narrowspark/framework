<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Providers\CacheServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Cron\Providers\CronServiceProvider;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class CronServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
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
        self::assertTrue(is_array($container->get('cron.commands')));
    }
}
