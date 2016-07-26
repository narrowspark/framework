<?php
declare(strict_types=1);
namespace Viserio\Console\Providers;

use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Viserio\Application\ServiceProvider;
use Viserio\Console\Application;
use Viserio\Console\Command\CommandResolver;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('command.resolver', function ($app) {
            return new CommandResolver($app);
        });

        $this->app->singleton('command', function ($app) {
            $app->bind('console.app.name', 'Narrowspark Cerebro');
            $app->bind('console.app.version', $app->getVersion());

            $console = new Application(
                $app,
                $app->get('events'),
                $app->get('console.app.version'),
                $app->get('console.app.name')
            );

            // Add auto-complete for Symfony Console application
            $console->add(new CompletionCommand());

            $console->addCommands($app->get('command.resolver')->commands());

            return $console;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'command',
            'command.resolver',
        ];
    }
}
