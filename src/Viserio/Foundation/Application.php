<?php
declare(strict_types=1);
namespace Viserio\Foundation;

use Closure;
use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
use Viserio\Contracts\Events\Dispatcher as DispatcherContract;
use Viserio\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\Contracts\Translation\TranslationManager;
use Viserio\Events\Providers\EventsServiceProvider;
use Viserio\Foundation\Http\Emitter;
use Viserio\Foundation\Providers\ConfigureLoggingProvider;
use Viserio\Log\Providers\LoggerServiceProvider;
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

        $this->registerBaseServiceProviders();

        /*
         * Here we are binding the paths configured in paths.php to the app. You
         * should not be changing these here. If you need to change these you
         * may do so within the paths.php file and they will be bound here.
         */
        $this->bindInstallPaths($paths);
        $this->registerCacheFilePaths();

        $this->registerBaseBindings();

        $config = $this->get(ConfigManagerContract::class);
        $config->set(
            'app.maintenance',
            file_exists($config->get('path.storage') . '/framework/down'),
            false
        );
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
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
        return $this->get(ConfigManagerContract::class)->get('app.locale');
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): ApplicationContract
    {
        $this->get(ConfigManagerContract::class)->set('app.locale', $locale);

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
     *
     * @codeCoverageIgnore
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->get(ConfigManagerContract::class)->get('path.base');
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function useEnvironmentPath(string $path): ApplicationContract
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function loadEnvironmentFrom(string $file): ApplicationContract
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function environmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function environmentFilePath(): string
    {
        return $this->environmentPath() . '/' . $this->environmentFile();
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
     *
     * @codeCoverageIgnore
     */
    public function isLocal(): bool
    {
        return $this->get('env') == 'local';
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function runningUnitTests(): bool
    {
        return $this->get('env') == 'testing';
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
            $this->get(ConfigManagerContract::class)->set(sprintf('path.%s', $key), realpath($value));
        }

        return $this;
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new ParsersServiceProvider());
        $this->register(new ConfigServiceProvider());
        $this->register(new EventsServiceProvider());
        $this->register(new LoggerServiceProvider());
        $this->register(new ConfigureLoggingProvider());
        $this->register(new RoutingServiceProvider());
    }

    /**
     * Register the basic bindings into the container.
     */
    protected function registerBaseBindings()
    {
        $app = $this;

        $this->singleton(ApplicationContract::class, function () use ($app) {
            return $app;
        });

        $this->alias(ApplicationContract::class, self::class);
        $this->alias(ApplicationContract::class, 'app');

        $this->singleton(Container::class, $this);

        $this->singleton(EmitterContract::class, Emitter::class);

        $this->singleton(EnvironmentDetector::class, EnvironmentDetector::class);
    }

    /**
     * Bind needed cache paths to our config manager.
     */
    protected function registerCacheFilePaths()
    {
        $config = $this->get(ConfigManagerContract::class);

        $config->set('patch.cached.config', $config->get('path.storage') . '/framework/cache/config.php');

        $config->set('patch.cached.commands', $config->get('path.storage') . '/framework/cache/commands.php');
    }
}
