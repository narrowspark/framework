<?php
declare(strict_types=1);
namespace Viserio\Foundation;

use Closure;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Foundation\Http\Emitter;
use Viserio\Parsers\Providers\ParsersServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Translation\TranslationManager;
use Viserio\Routing\Providers\RoutingServiceProvider;

class Application extends Container implements ApplicationContract
{
    /**
     * The Viserio framework version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    protected $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     *
     * @var string
     */
    protected $environmentPath;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * A custom callback used to configure Monolog.
     *
     * @var callable|null
     */
    protected $monologConfigurator;

    /**
     * Create a new application instance.
     *
     * Let's start make magic!
     *
     * @param array $paths
     */
    public function __construct(array $paths)
    {
        parent::__construct();

        /*
         * Here we are binding the paths configured in paths.php to the app. You
         * should not be changing these here. If you need to change these you
         * may do so within the paths.php file and they will be bound here.
         */
        $this->bindInstallPaths($paths);

        $this->registerBaseServiceProviders();

        $this->registerBaseBindings();
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param array $bootstrappers
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            if ($this->has(DispatcherContract::class)) {
                $this->get(DispatcherContract::class)->trigger(
                    'bootstrapping.' . str_replace('\\', '', $bootstrapper),
                    [$this]
                );
            }

            $this->make($bootstrapper)->bootstrap($this);

            if ($this->has(DispatcherContract::class)) {
                $this->get(DispatcherContract::class)->trigger(
                    'bootstrapped.' . str_replace('\\', '', $bootstrapper),
                    [$this]
                );
            }
        }
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->get(ConfigManager::class)->get('app.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->get(ConfigManager::class)->set('app.locale', $locale);

        if ($this->has(TranslationManager::class)) {
            $this->get(TranslationManager::class)->setLocale($locale);
        }

        $this->get(DispatcherContract::class)->trigger('locale.changed', [$locale]);
    }

    /**
     * Determine if application locale is the given locale.
     *
     * @param  string  $locale
     * @return bool
     */
    public function isLocale($locale)
    {
        return $this->getLocale() == $locale;
    }

    /**
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->get('path.base');
    }

    /**
     * Set the directory for the environment file.
     *
     * @param string $path
     *
     * @return $this
     */
    public function useEnvironmentPath(string $path): ApplicationContract
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * Set the environment file to be loaded during bootstrapping.
     *
     * @param string $file
     *
     * @return $this
     */
    public function loadEnvironmentFrom(string $file): ApplicationContract
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * Get the environment file the application is using.
     *
     * @return string
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string
     */
    public function environmentFilePath(): string
    {
        return $this->environmentPath() . '/' . $this->environmentFile();
    }

    /**
     * Determine if the application configuration is cached.
     *
     * @return bool
     */
    public function configurationIsCached(): bool
    {
        return file_exists($this->getCachedConfigPath());
    }

    /**
     * Detect the application's current environment.
     *
     * @param \Closure $callback
     *
     * @return string
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;

        $this->instance('env', (new EnvironmentDetector())->detect($callback, $args));

        return $this->get('env');
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * Determine if application is in local environment.
     *
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->get('env') == 'local';
    }

    /**
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests(): bool
    {
        return $this->get('env') == 'testing';
    }

    /**
     * Define a callback to be used to configure Monolog.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function configureMonologUsing(callable $callback): ApplicationContract
    {
        $this->monologConfigurator = $callback;

        return $this;
    }

    /**
     * Determine if the application has a custom Monolog configurator.
     *
     * @return bool
     */
    public function hasMonologConfigurator(): bool
    {
        return ! is_null($this->monologConfigurator);
    }

    /**
     * Get the custom Monolog configurator for the application.
     *
     * @return callable
     */
    public function getMonologConfigurator()
    {
        return $this->monologConfigurator;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path(): string
    {
        return $this->get('path.app');
    }

    /**
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath(): string
    {
        return $this->get('path.config');
    }

    /**
     * Get the path to the application routes files.
     *
     * @return string
     */
    public function routesPath(): string
    {
        return $this->get('path.route');
    }

    /**
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath(): string
    {
        return $this->get('path.database');
    }

    /**
     * Get the path to the language files.
     *
     * @return string
     */
    public function langPath(): string
    {
        return $this->get('path.lang');
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath(): string
    {
        return $this->get('path.public');
    }

    /**
     * Get the path to the base ../ directory.
     *
     * @return string
     */
    public function basePath(): string
    {
        return $this->get('path.base');
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath(): string
    {
        return $this->get('path.storage');
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath():string
    {
        return $this->storagePath() . '/framework/cache/config.php';
    }

    /**
     * Bind the installation paths to the config.
     *
     * @param array $paths
     *
     * @throws \Exception
     *
     * @return $this
     */
    protected function bindInstallPaths(array $paths)
    {
        // Each path key is prefixed with path
        // so that they have the consistent naming convention.
        foreach ($paths as $key => $value) {
            $this->instance(sprintf('path.%s', $key), realpath($value));
        }

        return $this;
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new ConfigServiceProvider());
        $this->register(new EventsServiceProvider());
        $this->register(new ParsersServiceProvider());
        $this->register(new RoutingServiceProvider());
    }

    /**
     * Register the basic bindings into the container.
     */
    protected function registerBaseBindings()
    {
        $this->singleton('app', function () {
            return $this;
        });

        $this->instance(Container::class, $this);

        $this->instance(ApplicationContract::class, $this);

        $this->singleton(EmitterContract::class, Emitter::class);
    }
}
