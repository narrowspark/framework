<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Viserio\Bridge\Twig\Providers\ConsoleCommandsServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Providers\ConsoleServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class ConsoleCommandsServiceProviderTest extends TestCase
{
    public function testGetServices()
    {
        $container = new Container();
        $container->register(new ConsoleServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new ConsoleCommandsServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'console' => [
                    'version' => '1',
                ],
            ],
        ]);

        $console  = $container->get(Application::class);
        $commands = $console->all();

        self::assertInstanceOf(CleanCommand::class, $commands['twig:clean']);
        self::assertInstanceOf(DebugCommand::class, $commands['twig:debug']);
        self::assertInstanceOf(LintCommand::class, $commands['twig:lint']);
    }
}
