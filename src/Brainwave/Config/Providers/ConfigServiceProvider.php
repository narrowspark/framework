<?php
namespace Brainwave\Config\Providers;

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
use Brainwave\Config\Manager as ConfigManager;
use Brainwave\Config\Repository;
use Brainwave\Filesystem\FileLoader;

/**
 * ConfigServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.5-dev
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
