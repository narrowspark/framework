<?php
namespace Viserio\Config\Providers;

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
 * @version     0.10.0
 */

use Viserio\Application\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;
use Viserio\Filesystem\FileLoader;

/**
 * ConfigServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.5
 */
class ConfigServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('config.repository', function () {
            return new Repository();
        });

        $this->app->singleton('config', function ($app) {
            return new ConfigManager(
                $app->get('config.repository'),
                new FileLoader($app->get('files'), $app->get('settings.path'))
            );
        });
    }
}
