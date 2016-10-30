<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Cron\Providers\CronServiceProvider;
use Viserio\Cron\Schedule;

class ConsoleServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new CronServiceProvider());

        $container->get('config')->set('cron', [
            'console' => 'cerebro',
            'mutex_path' => __DIR__ . '/..',
            'path' => __DIR__ . '..',
        ]);

        $this->assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new CronServiceProvider());

        $container->instance('options', [
            'console' => 'cerebro',
            'mutex_path' => __DIR__ . '..',
            'path' => __DIR__ . '..',
        ]);

        $this->assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new CronServiceProvider());

        $container->instance('viserio.cron.options', [
            'console' => 'cerebro',
            'mutex_path' => __DIR__ . '/..',
            'path' => __DIR__ . '..',
        ]);

        $this->assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }
}
