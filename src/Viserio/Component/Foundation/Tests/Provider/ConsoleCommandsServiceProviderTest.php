<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Config\Command\ConfigCacheCommand;
use Viserio\Component\Foundation\Config\Command\ConfigClearCommand;
use Viserio\Component\Foundation\Console\Command\DownCommand;
use Viserio\Component\Foundation\Console\Command\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Command\ServeCommand;
use Viserio\Component\Foundation\Console\Command\UpCommand;
use Viserio\Component\Foundation\Provider\ConsoleCommandsServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends MockeryTestCase
{
    public function testGetExtensions(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('isLocal')
            ->once()
            ->andReturn(true);

        $container->instance(KernelContract::class, $kernel);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        static::assertInstanceOf(UpCommand::class, $commands['app:up']);
        static::assertInstanceOf(DownCommand::class, $commands['app:down']);
        static::assertInstanceOf(KeyGenerateCommand::class, $commands['key:generate']);
        static::assertInstanceOf(ServeCommand::class, $commands['serve']);
        static::assertInstanceOf(ConfigCacheCommand::class, $commands['config:cache']);
        static::assertInstanceOf(ConfigClearCommand::class, $commands['config:clear']);
    }

    public function testGetDimensions(): void
    {
        static::assertSame(['viserio', 'console'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        static::assertSame(
            [
                'lazily_commands' => [
                    'app:down'     => DownCommand::class,
                    'app:up'       => UpCommand::class,
                    'config:cache' => ConfigCacheCommand::class,
                    'config:clear' => ConfigClearCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
