<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices(): array
    {
        return [
            Application::class => [self::class, 'createConsoleCommands'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        $console = \is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($console !== null) {
            /* @var Application $console */
            $console->addCommands([
                new OptionDumpCommand(),
            ]);
        }

        return $console;
    }
}
