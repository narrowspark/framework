<?php

namespace Brainwave\Database\Providers;

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
use Brainwave\Database\Connection\ConnectionFactory;
use Brainwave\Database\DatabaseManager;
use Brainwave\Database\Query;

/**
 * Database ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1-dev
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        if ($this->app->get('config')->get('database::frozen')) {
            $this->app->bind('db', function () {
                return 'Database is frozen.';
            });
        } else {
            $this->registerConnectionFactory();

            // The database manager is used to resolve various connections, since multiple
            // connections might be managed. It also implements the connection resolver
            // interface which may be used by other components requiring connections.
            $this->app->singleton('db', function ($app) {
                return new DatabaseManager(
                    $app,
                    $app->get('db.factory')
                );
            });

            $this->registerDatabaseQuery();
        }
    }

    protected function registerDatabaseQuery()
    {
        $type = $this->app->get('db')->getConnections();

        $$this->app->singleton('db.query', function ($app) {
            return new Query($app->get('db')->connection());
        });

        foreach ($type as $driver => $value) {
            $this->app->bind(sprintf('db.%d.query', $driver), function ($app) use ($driver) {
                return new Query($app->get('db')->connection($driver));
            });
        }
    }

    protected function registerConnectionFactory()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
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
            'db',
            'db.factory',
            'db.query',
        ];
    }
}
