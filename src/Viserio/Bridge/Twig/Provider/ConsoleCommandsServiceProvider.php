<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

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
                'twig:debug' => DebugCommand::class,
                'lint:twig'  => LintCommand::class,
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
            $twig = $container->get(Environment::class);

            $console->addCommands([
                new DebugCommand($twig),
                new LintCommand($twig),
            ]);
        }

        return $console;
    }
}
