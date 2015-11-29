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
 * @version     0.10.0
 */

use Viserio\Application\ServiceProvider;
use Viserio\Support\Debug\Dumper;

/**
 * DebugServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
 */
class DebugServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind('dumper', function () {
            return new Dumper();
        });
    }
}
