<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Console\Application;
use Viserio\Component\Foundation\Console\Commands\DownCommand;
use Viserio\Component\Foundation\Console\Commands\KeyGenerateCommand;
use Viserio\Component\Foundation\Console\Commands\UpCommand;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Application::class => [self::class, 'createConsoleCommands'],
        ];
    }

    /**
     * Extend viserio console with commands.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Console\Application
     */
    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?Application
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();

            $console->addCommands([
                new DownCommand(),
                new UpCommand(),
                new KeyGenerateCommand(),
            ]);

            return $console;
        }
        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }
}
