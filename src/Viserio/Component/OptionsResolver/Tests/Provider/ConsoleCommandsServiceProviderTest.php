<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\OptionsResolver\Provider\ConsoleCommandsServiceProvider;

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

        static::assertInstanceOf(OptionDumpCommand::class, $commands['option:dump']);
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
                    'option:dump' => OptionDumpCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
