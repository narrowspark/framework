<?php
namespace Viserio\Support\Providers;

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

use Viserio\Application\ServiceProvider;
use Viserio\Support\Autoloader;

/**
 * AutoloaderServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class AutoloaderServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('autoloader', function () {
            return new Autoloader();
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
            'autoloader',
        ];
    }
}
