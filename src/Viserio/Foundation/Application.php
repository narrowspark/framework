<?php
declare(strict_types=1);
namespace Viserio\Foundation;

use Closure;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\Contracts\Translation\TranslationManager;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Foundation\Http\Emitter;
use Viserio\Parsers\Providers\ParsersServiceProvider;
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
        $this->registerBaseServiceProviders();

        $this->bindInstallPaths($paths);
        $this->registerCacheFilePaths();

        $this->registerBaseBindings();
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrappers)
    {
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->get(DispatcherContract::class)->trigger(
                'bootstrapping.' . str_replace('\\', '', $bootstrapper),
                [$this]
            );

            $this->make($bootstrapper)->bootstrap($this);

            $this->get(DispatcherContract::class)->trigger(
                'bootstrapped.' . str_replace('\\', '', $bootstrapper),
                [$this]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->get(ConfigManager::class)->get('app.locale');
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): ApplicationContract
    {
        $this->get(ConfigManager::class)->set('app.locale', $locale);

        if ($this->has(TranslationManager::class)) {
            $this->get(TranslationManager::class)->setLocale($locale);
        }

        $this->get(DispatcherContract::class)->trigger('locale.changed', [$locale]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocale(string $locale): bool
    {
        return $this->getLocale() == $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->get(ConfigManager::class)->get('path.base');
    }

    /**
     * {@inheritdoc}
     */
    public function useEnvironmentPath(string $path): ApplicationContract
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEnvironmentFrom(string $file): ApplicationContract
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * {@inheritdoc}
     */
    public function environmentFilePath(): string
    {
        return $this->environmentPath() . '/' . $this->environmentFile();
    }

    /**
     * {@inheritdoc}
     */
    public function configurationIsCached(): bool
    {
        return file_exists($this->get(ConfigManager::class)->get('patch.cached.config'));
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;

        $this->instance('env', $this->get(EnvironmentDetector::class)->detect($callback, $args));

        return $this->get('env');
    }

    /**
     * {@inheritdoc}
     */
    public function isLocal(): bool
    {
        return $this->get('env') == 'local';
    }

    /**
     * {@inheritdoc}
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
            $this->get(ConfigManager::class)->set(sprintf('path.%s', $key), realpath($value));
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

        $this->singleton(Container::class, $this);

        $this->singleton(ApplicationContract::class, $this);

        $this->singleton(EmitterContract::class, Emitter::class);

        $this->singleton(EnvironmentDetector::class, EnvironmentDetector::class);
    }

    /**
     * Bind needed cache paths to our config manager.
     */
    protected function registerCacheFilePaths()
    {
        $config = $this->get(ConfigManager::class);

        $config->set('patch.cached.config', $config->get('path.storage') . '/framework/cache/config.php');

        $config->set('patch.cached.commands', $config->get('path.storage') . '/framework/cache/commands.php');
    }
}
