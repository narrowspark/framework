<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Foundation\Console\Commands\DownCommand;
use Viserio\Component\Foundation\Console\Commands\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Commands\UpCommand;
use Viserio\Component\Foundation\Providers\ConsoleCommandsServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(UpCommand::class, $commands['up']);
        // self::assertInstanceOf(DownCommand::class, $commands['down']);
        // self::assertInstanceOf(KeyGenerateCommand::class, $commands['cron:list']);
    }
}
