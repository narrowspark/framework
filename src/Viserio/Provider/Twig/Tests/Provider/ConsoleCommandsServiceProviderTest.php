<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Provider\ConsoleCommandsServiceProvider;

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

        $this->assertInstanceOf(CleanCommand::class, $commands['twig:clear']);
        $this->assertInstanceOf(DebugCommand::class, $commands['twig:debug']);
        $this->assertInstanceOf(LintCommand::class, $commands['lint:twig']);
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
                    'twig:debug' => DebugCommand::class,
                    'lint:twig'  => LintCommand::class,
                    'twig:clear' => CleanCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
