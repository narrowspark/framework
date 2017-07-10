<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Routing\Command\RouteListCommand;

class ConsoleCommandsServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            Application::class => [self::class, 'extendConsole'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface           $container
     * @param null|\Viserio\Component\Console\Application $console
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function extendConsole(
        ContainerInterface $container,
        ?Application $console = null
    ): ?Application {
        if ($console !== null && $container->has(RouterContract::class)) {
            $console->addCommands([
                new RouteListCommand($container->get(RouterContract::class)),
            ]);
        }

        return $console;
    }
}
