<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Provider\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Provider\ConsoleCommandsServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices(): void
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(CleanCommand::class, $commands['twig:clean']);
        self::assertInstanceOf(DebugCommand::class, $commands['twig:debug']);
        self::assertInstanceOf(LintCommand::class, $commands['twig:lint']);
    }
}
