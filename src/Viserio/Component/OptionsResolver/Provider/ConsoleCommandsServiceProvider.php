<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;

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
        if ($console !== null) {
            /* @var Application $console */
            $console->addCommands([
                new OptionDumpCommand(),
            ]);
        }

        return $console;
    }
}
