<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Symfony\Component\Console\Application as SymfonyConsole;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class ConsoleServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'createCerebro'],
            SymfonyConsole::class      => function (ContainerInterface $container) {
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
    public function getDimensions(): iterable
    {
        return ['viserio', 'console'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['version'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'name' => 'Cerebro',
        ];
    }

    public static function createCerebro(ContainerInterface $container): ApplicationContract
    {
        self::resolveOptions($container);

        $console = new Application(
            $container,
            self::$options['version'],
            self::$options['name']
        );

        if ($container->has(EventManagerContract::class)) {
            $console->setEventManager($container->get(EventManagerContract::class));
        }

        return $console;
    }

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
