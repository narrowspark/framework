<?php
declare(strict_types=1);
namespace Viserio\Application\Providers;

use Viserio\Application\EnvironmentDetector;
use Viserio\Application\ServiceProvider;

/**
 * ApplicationServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
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
        $this->registerEntvironment();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'environment',
            'config',
            'alias',
        ];
    }

    protected function registerEntvironment()
    {
        $this->app->singleton('environment', function () {
            return new EnvironmentDetector();
        });
    }

    protected function registerConfig()
    {
        // Settings
        $this->app->register(
            'Viserio\Config\Providers\ConfigServiceProvider',
            ['settings.path' => sprintf('%s', $this->app->configPath())]
        );

        //Load config files
        foreach ($this->config as $file => $setting) {
            $this->app->get('config')->bind(
                $file . '.' . $setting['ext'],
                $setting['group'],
                null,
                null
            );
        }
    }
}
