<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Foundation\Commands\DownCommand;
use Viserio\Component\Foundation\Commands\KeyGenerateCommand;
use Viserio\Component\Foundation\Commands\UpCommand;
use Viserio\Component\Foundation\Providers\ConsoleCommandsServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
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

        self::assertInstanceof(UpCommand::class, $commands['up']);
        // self::assertInstanceof(DownCommand::class, $commands['down']);
        // self::assertInstanceof(KeyGenerateCommand::class, $commands['cron:list']);
    }
}
