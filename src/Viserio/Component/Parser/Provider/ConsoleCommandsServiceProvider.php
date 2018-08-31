<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Parser\Command\XliffLintCommand;
use Viserio\Component\Parser\Command\YamlLintCommand;

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
                'lint:xliff' => XliffLintCommand::class,
                'lint:yaml'  => YamlLintCommand::class,
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
        if ($console !== null) {
            $console->add(new XliffLintCommand());
        }

        if (\class_exists(Yaml::class)) {
            $console->add(new YamlLintCommand());
        }

        return $console;
    }
}
