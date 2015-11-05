<?php
namespace Brainwave\Http\Providers;

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
use Brainwave\Http\Response;

/**
 * ResponseServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class ResponseServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('response', function () {
            return new Response();
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
            'response',
        ];
    }
}
