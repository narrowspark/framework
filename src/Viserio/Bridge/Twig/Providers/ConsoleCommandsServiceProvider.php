<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Viserio\Component\Console\Application;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'createConsoleCommands'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();

            $console->addCommands([
                new CleanCommand(),
                new DebugCommand(),
                new LintCommand(),
            ]);

            return $console;
        }
        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }
}
