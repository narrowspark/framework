<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Component\Foundation\Commands\DownCommand;
use Viserio\Component\Foundation\Commands\KeyGenerateCommand;
use Viserio\Component\Foundation\Commands\UpCommand;

class ConsoleCommandsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ApplicationContract::class => [self::class, 'createConsoleCommands'],
        ];
    }

    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?ApplicationContract
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();

            $console->addCommands([
                // new DownCommand(),
                new UpCommand(),
                // new KeyGenerateCommand(),
            ]);

            return $console;
        }

        return null;
    }
}
