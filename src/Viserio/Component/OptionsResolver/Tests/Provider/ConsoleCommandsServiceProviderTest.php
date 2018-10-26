<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\OptionsResolver\Command\OptionReaderCommand;
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

        $this->assertInstanceOf(OptionDumpCommand::class, $commands['option:dump']);
        $this->assertInstanceOf(OptionReaderCommand::class, $commands['option:read']);
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
                    'option:dump' => OptionDumpCommand::class,
                    'option:read' => OptionReaderCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
