<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ConsoleServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            Application::class            => [self::class, 'createCerebro'],
            SymfonyConsole::class         => function (ContainerInterface $container) {
                return $container->get(Application::class);
            },
            'console'                     => function (ContainerInterface $container) {
                return $container->get(Application::class);
            },
            'cerebro'                     => function (ContainerInterface $container) {
                return $container->get(Application::class);
            },
            ContainerCommandLoader::class => [self::class, 'createContainerCommandLoader'],
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
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'console'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'lazily_commands' => [],
        ];
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

        if ($container->has(ContainerCommandLoader::class)) {
            $console->setCommandLoader($container->get(ContainerCommandLoader::class));
        }

        return $console;
    }

    /**
     * Create a new console application instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Symfony\Component\Console\CommandLoader\ContainerCommandLoader
     */
    public static function createContainerCommandLoader(ContainerInterface $container): ContainerCommandLoader
    {
        $options = self::resolveOptions($container);

        return new ContainerCommandLoader($container, $options['lazily_commands']);
    }
}
