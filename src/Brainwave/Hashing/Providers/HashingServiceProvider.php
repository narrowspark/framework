<?php

namespace Brainwave\Hashing\Providers;

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
 * @version     0.9.8-dev
 */

use Brainwave\Application\ServiceProvider;
use Brainwave\Hashing\Generator as HashGenerator;
use Brainwave\Hashing\Password;
use RandomLib\Factory as RandomLib;

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
            return new HashGenerator($app->get('hash.rand.generator'));
        });
    }

    protected function registerRand()
    {
        $this->app->singleton('hash.rand', function () {
            return new RandomLib();
        });
    }

    protected function registerRandGenerator()
    {
        $this->app->bind('hash.rand.generator', function ($app) {
            $generatorStrength = ucfirst(
                $app->get('config')->get(
                    'app::hash.generator.strength',
                    'Medium'
                )
            );

            $generator = sprintf('get%sStrengthGenerator', $generatorStrength);

            return $app->get('hash.rand')->$generator();
        });
    }

    protected function registerPassword()
    {
        $this->app->singleton('password', function () {
            return new Password();
        });
    }

    public function aliases()
    {
        return ['hash.rand' => 'RandomLib\Factory'];
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
            'hash.rand',
            'hash.rand.generator',
            'password',
        ];
    }
}
