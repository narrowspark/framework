<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Command\ConfigCacheCommand;
use Viserio\Component\Config\Command\ConfigClearCommand;
use Viserio\Component\Config\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $console  = $container->get(Application::class);
        $commands = $console->all();

        $this->assertInstanceOf(ConfigCacheCommand::class, $commands['config:cache']);
        $this->assertInstanceOf(ConfigClearCommand::class, $commands['config:clear']);
    }

    public function testGetDimensions(): void
    {
        $this->assertSame(['viserio', 'console'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions(): void
    {
        $this->assertSame(
            [
                'lazily_commands' => [
                    'config:cache' => ConfigCacheCommand::class,
                    'config:clear' => ConfigClearCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
