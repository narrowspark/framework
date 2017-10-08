<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Console\Command\DownCommand;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Command\ServeCommand;
use Viserio\Component\Foundation\Console\Command\UpCommand;
use Viserio\Component\Foundation\Provider\ConsoleCommandsServiceProvider;

class ConsoleCommandsServiceProviderTest extends MockeryTestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('isLocal')
            ->once()
            ->andReturn(true);

        $container->instance(KernelContract::class, $kernel);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(UpCommand::class, $commands['app:up']);
        self::assertInstanceOf(DownCommand::class, $commands['app:down']);
        self::assertInstanceOf(KeyGenerateCommand::class, $commands['key:generate']);
        self::assertInstanceOf(ServeCommand::class, $commands['serve']);
    }
}
