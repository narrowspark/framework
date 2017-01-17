<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Providers;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cache\Providers\CacheServiceProvider;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Cron\Providers\CronServiceProvider;
use Viserio\Component\Cron\Schedule;

class CronServiceProviderTest extends TestCase
{
    use MockeryTrait;

    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new CacheServiceProvider());
        $container->register(new CronServiceProvider());

        $container->get('config')->set('cron', [
            'console'    => 'cerebro',
            'mutex_path' => __DIR__ . '/..',
            'path'       => __DIR__ . '..',
        ]);

        self::assertInstanceOf(Schedule::class, $container->get(Schedule::class));
        self::assertTrue(is_array($container->get('cron.commands')));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new CronServiceProvider());

        $container->instance('options', [
            'console'    => 'cerebro',
            'mutex_path' => __DIR__ . '..',
            'path'       => __DIR__ . '..',
        ]);
        $container->instance(CacheItemPoolInterface::class, $this->mock(CacheItemPoolInterface::class));

        self::assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new CronServiceProvider());

        $container->instance('viserio.cron.options', [
            'console'    => 'cerebro',
            'mutex_path' => __DIR__ . '/..',
            'path'       => __DIR__ . '..',
        ]);
        $container->instance(CacheItemPoolInterface::class, $this->mock(CacheItemPoolInterface::class));

        self::assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }
}
