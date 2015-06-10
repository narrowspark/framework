<?php

namespace Brainwave\Support\Providers;

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
use Brainwave\Support\Arr;
use Brainwave\Support\Helper;
use Brainwave\Support\StaticalProxyResolver;
use Brainwave\Support\Str;
use RandomLib\Factory as RandomLib;

/**
 * SupportServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class SupportServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerHelper();
        $this->registerArr();
        $this->registerStr();
        $this->registerStaticalProxyResolver();
    }

    /**
     * Register Helpers.
     *
     * @return \Brainwave\Support\Helper|null
     */
    protected function registerHelper()
    {
        $this->app->singleton('helper', function () {
            return new Helper();
        });
    }

    /**
     * Register Arr.
     *
     * @return \Brainwave\Support\Arr|null
     */
    protected function registerArr()
    {
        $this->app->singleton('arr', function () {
            return new Arr();
        });
    }

    /**
     * Register Str.
     *
     * @return \Brainwave\Support\Str|null
     */
    protected function registerStr()
    {
        $this->app->singleton('str', function () {
            return new Str();
        });
    }

    protected function registerStaticalProxyResolver()
    {
        $this->app->singleton('statical.resolver', function () {
            return new StaticalProxyResolver();
        });
    }

    /**
     * Register randomlib.
     *
     * @return \RandomLib\Factory|null
     */
    protected function registerRand()
    {
        $this->app->singleton('rand', function () {
            return new RandomLib();
        });
    }

    /**
     * Register randomlib generator.
     *
     * @return \RandomLib\Factory|null
     */
    protected function registerRandGenerator()
    {
        $this->app->bind('rand.generator', function ($app) {
            $generatorStrength = ucfirst(
                $app->get('config')->get(
                    'app::generator.strength',
                    'Medium'
                )
            );

            $generator = sprintf('get%sStrengthGenerator', $generatorStrength);

            return $app->get('rand')->$generator();
        });
    }

    public function aliases()
    {
        return ['rand' => 'RandomLib\Factory'];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'arr',
            'helper',
            'rand',
            'rand.generator',
            'str',
            'statical.resolver',
        ];
    }
}
