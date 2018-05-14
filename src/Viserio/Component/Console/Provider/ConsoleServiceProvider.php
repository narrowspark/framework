<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Provider;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;

class ConsoleServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            Application::class    => [self::class, 'createCerebro'],
            SymfonyConsole::class => function (ContainerInterface $container) {
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

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * Create a new console application instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Console\Application
     */
    public static function createCerebro(ContainerInterface $container): Application
    {
        $console = new Application();
        $console->setContainer($container);

        if ($container->has(EventManagerContract::class)) {
            $console->setEventManager($container->get(EventManagerContract::class));
        }

        return $console;
    }
}
