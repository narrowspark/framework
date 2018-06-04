<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Parser\Command\XliffLintCommand;
use Viserio\Component\Parser\Provider\ConsoleCommandsServiceProvider;

/**
 * @internal
 */
final class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $console  = $container->get(Application::class);
        $commands = $console->all();

        $this->assertInstanceOf(XliffLintCommand::class, $commands['lint:xliff']);
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
                    'lint:xliff' => XliffLintCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
