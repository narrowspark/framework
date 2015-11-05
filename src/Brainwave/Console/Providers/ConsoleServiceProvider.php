<?php
namespace Brainwave\Console\Providers;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Application\ServiceProvider;
use Brainwave\Console\Application;
use Brainwave\Console\Command\CommandResolver;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;

/**
 * ConsoleServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
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

            $console = new Application($app, $app->get('events'));

            $console->setName($app->get('console.app.name'));
            $console->setVersion($app->get('console.app.version'));

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
    public function provides()
    {
        return [
            'command',
            'command.resolver',
        ];
    }
}
