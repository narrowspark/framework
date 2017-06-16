<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Foundation\Console\Command\DownCommand;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Command\UpCommand;
use Viserio\Component\Foundation\Provider\ConsoleCommandsServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(UpCommand::class, $commands['up']);
        self::assertInstanceOf(DownCommand::class, $commands['down']);
        self::assertInstanceOf(KeyGenerateCommand::class, $commands['key:generate']);
    }
}
