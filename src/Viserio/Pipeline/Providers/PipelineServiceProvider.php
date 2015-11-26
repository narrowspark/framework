<?php
namespace Viserio\Pipeline\Providers;

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
use Viserio\Pipeline\Hub;

/**
 * PipelineServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class PipelineServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('hub', function ($app) {
            return new Hub($app);
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
            'hub',
        ];
    }
}
