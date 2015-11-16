<?php
namespace Viserio\Events\Providers;

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
 * @version     0.10-dev
 */

use Viserio\Application\ServiceProvider;
use Viserio\Loop\Loop;

/**
 * LoopServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
class LoopServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('loop', function ($app) {
            $loop = new Loop();
            $loop->setContainer($app);

            return $loop;
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
            'loop',
        ];
    }
}
