<?php
namespace Viserio\Http\Providers;

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

use Symfony\Component\HttpFoundation\RequestStack;
use Viserio\Application\ServiceProvider;
use Viserio\Http\Request;

/**
 * RequestServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class RequestServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('request', function () {
            return new Request();
        });

        $this->app->singleton('stack.request', function () {
            return new RequestStack();
        });
    }

    public function aliases()
    {
        return ['stack.request' => 'Symfony\Component\HttpFoundation\RequestStack'];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'request',
            'stack.request',
        ];
    }
}
