<?php
declare(strict_types=1);
namespace Viserio\Console\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Viserio\Console\Application;

class ConsoleServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.console';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'createCerebro'],
            'console.app.name' => [self::class, 'createConsoleName'],
            'console.app.version' => [self::class, 'createConsoleVersion'],
            'console' => function (ContainerInterface $container) {
                return $container->get(Application::class);
            },
            'cerebro' => function (ContainerInterface $container) {
                return $container->get(Application::class);
            },
        ];
    }

    public static function createCerebro(ContainerInterface $container): Application
    {
        $console = new Application(
            $container,
            $container->get('console.app.version'),
            $container->get('console.app.name')
        );

        // Add auto-complete for Symfony Console application
        $console->add(new CompletionCommand());

        return $console;
    }

    public static function createConsoleName(ContainerInterface $container): string
    {
        return self::get($container, 'app.name', 'Cerebro');
    }

    public static function createConsoleVersion(ContainerInterface $container): string
    {
        return self::get($container, 'app.version');
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
