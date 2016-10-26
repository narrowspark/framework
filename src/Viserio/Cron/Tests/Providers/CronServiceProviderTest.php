<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Cron\Schedule;
use Viserio\Container\Container;
use Viserio\Cron\Providers\CronServiceProvider;

class ConsoleServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new CronServiceProvider());

        $container->get('config')->set('cron', [
            'console' => 'cerebro',
        ]);

        $this->assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new CronServiceProvider());

        $container->instance('options', [
            'console' => 'cerebro',
        ]);

        $this->assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new CronServiceProvider());

        $container->instance('viserio.cron.options', [
            'console' => 'cerebro',
        ]);

        $this->assertInstanceOf(Schedule::class, $container->get(Schedule::class));
    }
}
