<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Component\Console\Application;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Command\LintCommand;

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
        $console = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($console !== null) {
            $console->add(new CleanCommand());

            if (class_exists(DebugCommand::class)) {
                $console->addCommands([
                    new DebugCommand(),
                    new LintCommand(),
                ]);
            }
        }

        return $console;
    }
}
