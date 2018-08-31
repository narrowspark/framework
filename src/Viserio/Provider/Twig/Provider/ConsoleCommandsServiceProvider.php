<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\View\Finder as FinderContract;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Command\LintCommand;

class ConsoleCommandsServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
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
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'console'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'lazily_commands' => [
                'lint:twig'  => LintCommand::class,
                'twig:clear' => CleanCommand::class,
            ],
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
    public static function extendConsole(ContainerInterface $container, ?Application $console = null): ?Application
    {
        if ($console !== null && $container->has(Environment::class)) {
            $console->add(new CleanCommand($container->get('config')));

            if (\class_exists(DebugCommand::class)) {
                $twig = $container->get(Environment::class);

                $console->addCommands([
                    new LintCommand($twig, $container->get(FinderContract::class), $container->get('config')),
                ]);
            }
        }

        return $console;
    }
}
