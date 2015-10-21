<?php

namespace Brainwave\Application\Providers;

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

use Brainwave\Application\AliasLoader;
use Brainwave\Application\EnvironmentDetector;
use Brainwave\Application\ServiceProvider;

/**
 * ApplicationServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Narrowspark config files.
     *
     * @var array
     */
    protected $config = [
        'app' => [
            'ext' => 'php',
            'group' => 'app',
        ],
        'mail' => [
            'ext' => 'php',
            'group' => 'mail',
        ],
        'cache' => [
            'ext' => 'php',
            'group' => 'cache',
        ],
        'services' => [
            'ext' => 'php',
            'group' => 'services',
        ],
        'session' => [
            'ext' => 'php',
            'group' => 'session',
        ],
        'cookies' => [
            'ext' => 'php',
            'group' => 'cookies',
        ],
        'view' => [
            'ext' => 'php',
            'group' => 'view',
        ],
        'autoload' => [
            'ext' => 'php',
            'group' => 'autoload',
        ],
        'database' => [
            'ext' => 'php',
            'group' => 'database',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerAliasLoader();
        $this->registerEntvironment();
    }

    protected function registerEntvironment()
    {
        $this->app->singleton('environment', function () {
            return new EnvironmentDetector();
        });
    }

    protected function registerAliasLoader()
    {
        $this->app->singleton('alias', function () {
            return new AliasLoader();
        });
    }

    protected function registerConfig()
    {
        // Settings
        $this->app->register(
            'Brainwave\Config\Providers\ConfigServiceProvider',
            ['settings.path' => sprintf('%s', $this->app->configPath())]
        );

        //Load config files
        foreach ($this->config as $file => $setting) {
            $this->app->get('config')->bind(
                $file.'.'.$setting['ext'],
                $setting['group'],
                null,
                null
            );
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'environment',
            'alias',
        ];
    }
}
