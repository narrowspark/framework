<?php
declare(strict_types=1);
namespace Viserio\Console\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Console\Application;
use Viserio\Contracts\Console\Application as ApplicationContract;

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
            ApplicationContract::class => function (ContainerInterface $container) {
                return $container->get(Application::class);
            },
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
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('console');
        } else {
            $config = self::get($container, 'options');
        }

        $console = new Application(
            $container,
            $config['version'],
            $config['name'] ?? 'Cerebro'
        );

        // Add auto-complete for Symfony Console application
        $console->add(new CompletionCommand());

        return $console;
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
