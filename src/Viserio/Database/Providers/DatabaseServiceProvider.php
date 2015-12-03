<?php
namespace Viserio\Database\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\Database\Connection\ConnectionFactory;
use Viserio\Database\DatabaseManager;
use Viserio\Database\Query;

/**
 * Database ServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1
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
