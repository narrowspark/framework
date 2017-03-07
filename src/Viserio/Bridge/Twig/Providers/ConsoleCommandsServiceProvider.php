<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Commands\LintCommand;

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
                new CleanCommand(),
                new DebugCommand(),
                new LintCommand(),
            ]);

            return $console;
        }

        return null;
    }
}
