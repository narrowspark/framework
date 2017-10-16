<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Bridge\Twig\Provider\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(DebugCommand::class, $commands['twig:debug']);
        self::assertInstanceOf(LintCommand::class, $commands['twig:lint']);
    }

    public function testGetDimensions()
    {
        self::assertSame(['viserio', 'console'], ConsoleCommandsServiceProvider::getDimensions());
    }

    public function testGetDefaultOptions()
    {
        self::assertSame(
            [
                'lazily_commands' => [
                    'twig:debug' => DebugCommand::class,
                    'twig:lint'  => LintCommand::class,
                ],
            ],
            ConsoleCommandsServiceProvider::getDefaultOptions()
        );
    }
}
