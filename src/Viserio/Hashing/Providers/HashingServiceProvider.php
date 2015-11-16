<?php
namespace Viserio\Hashing\Providers;

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
use Viserio\Hashing\Generator as HashGenerator;
use Viserio\Hashing\Password;

/**
 * HashingServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class HashingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerRand();
        $this->registerRandGenerator();
        $this->registerHashGenerator();

        $this->registerPassword();
    }

    protected function registerHashGenerator()
    {
        $this->app->singleton('hash', function ($app) {
            return new HashGenerator($app->get('rand.generator'));
        });
    }

    protected function registerPassword()
    {
        $this->app->singleton('password', function () {
            return new Password();
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
            'hash',
            'password',
        ];
    }
}
