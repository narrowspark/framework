<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Contracts\Foundation\Emitter as EmitterContract;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Translation\TranslationManager;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Foundation\Http\Emitter;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\Providers\LoggerServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;

class Application extends Container implements ApplicationContract
{
    /**
     * The Viserio framework version.
     *
     * @var string
     */
    public const VERSION = '1.0.0';

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

        $config = $this->get(RepositoryContract::class);
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
            $this->get(EventManagerContract::class)->trigger(
                'bootstrapping.' . str_replace('\\', '', $bootstrapper),
                $this
            );

            $this->make($bootstrapper)->bootstrap($this);

            $this->get(EventManagerContract::class)->trigger(
                'bootstrapped.' . str_replace('\\', '', $bootstrapper),
                $this
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
        return $this->get(RepositoryContract::class)->get('app.locale');
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): ApplicationContract
    {
        $this->get(RepositoryContract::class)->set('app.locale', $locale);

        if ($this->has(TranslationManager::class)) {
            $this->get(TranslationManager::class)->setLocale($locale);
        }

        $this->get(EventManagerContract::class)->trigger('locale.changed', $this, ['locale' => $locale]);

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
    public function getFallbackLocale(): string
    {
        return $this->get(RepositoryContract::class)->get('app.fallback_locale');
    }

    /**
     * {@inheritdoc}
     */
    public function hasLocale(string $locale): bool
    {
        return in_array($locale, $this->get(RepositoryContract::class)->get('app.locales'));
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function environmentPath(): string
    {
        return $this->environmentPath ?: $this->get(RepositoryContract::class)->get('path.base');
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
     * Determine if we are running in the console.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
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
            $this->get(RepositoryContract::class)->set(sprintf('path.%s', $key), realpath($value));
        }

        return $this;
    }

    /**
     * Register all of the base service providers.
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new OptionsResolverServiceProvider());
        $this->register(new ParsersServiceProvider());
        $this->register(new ConfigServiceProvider());

        $config = $this->get(RepositoryContract::class);
        $config->setLoader($this->get(LoaderContract::class));

        $this->register(new EventsServiceProvider());
        $this->register(new LoggerServiceProvider());
        $this->register(new ConfigureLoggingServiceProvider());
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
        $config = $this->get(RepositoryContract::class);

        $config->set('patch.cached.config', $config->get('path.storage') . '/framework/cache/config.php');
    }
}
