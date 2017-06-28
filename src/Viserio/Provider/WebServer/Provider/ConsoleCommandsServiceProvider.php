<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Component\Console\Application;
use Viserio\Provider\Twig\Command\CleanCommand;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'extendConsoleWithCommands'],
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
    public static function extendConsoleWithCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        $console = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($console !== null) {
        }

        return $console;
    }
}
