<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use ReflectionObject;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Translation\TranslationManager;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;
use Viserio\Component\Foundation\Events\LocaleChangedEvent;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\Providers\LoggerServiceProvider;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

abstract class AbstractKernel implements KernelContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The kernel version.
     *
     * @var string
     */
    public const VERSION = '1.0.0-DEV';

    /**
     * The kernel version id.
     *
     * @var int
     */
    public const VERSION_ID  = 10000;

    /**
     * The kernel extra version.
     *
     * @var string
     */
    public const EXTRA_VERSION = 'DEV';

    /**
     * Container instance.
     *
     * @var \Viserio\Component\Contracts\Container\Container
     */
    protected $container;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    protected $hasBeenBootstrapped = false;

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * Project path.
     *
     * @var string
     */
    protected $projectDir;

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
     * Create a new application instance.
     *
     * Let's start make magic!
     */
    public function __construct()
    {
        $this->projectDir = $this->getProjectDir();
    }

    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Container\Container
     */
    public function getContainer(): ContainerContract
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $container = $this->getContainer();
        $events    = $container->get(EventManagerContract::class);

        foreach ($bootstrappers as $bootstrapper) {
            $events->trigger(new BootstrappingEvent($bootstrapper, $this));

            $container->make($bootstrapper)->bootstrap($this);

            $events->trigger(new BootstrappedEvent($bootstrapper, $this));
        }

        $this->hasBeenBootstrapped = true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * Boots the current kernel.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->initializeContainer();

        $this->registerBaseServiceProviders();

        $this->registerBaseBindings();

        $this->booted = true;
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): KernelContract
    {
        $container = $this->getContainer();

        $container->get(RepositoryContract::class)->set('viserio.app.locale', $locale);

        if ($container->has(TranslationManager::class)) {
            $container->get(TranslationManager::class)->setLocale($locale);
        }

        $container->get(EventManagerContract::class)->trigger(new LocaleChangedEvent($this, $locale));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): string
    {
        return $this->getContainer()->get(RepositoryContract::class)->get('viserio.app.locale', 'en');
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale(): string
    {
        return $this->getContainer()->get(RepositoryContract::class)->get('viserio.app.fallback_locale', 'en');
    }

    /**
     * {@inheritdoc}
     */
    public function isLocal(): bool
    {
        return $this->getContainer()->get(RepositoryContract::class)->get('viserio.app.env') == 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function runningUnitTests(): bool
    {
        return $this->getContainer()->get(RepositoryContract::class)->get('viserio.app.env') == 'testing';
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance(): bool
    {
        return file_exists($this->getStoragePath('framework/down'));
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     */
    public function getProjectDir(): string
    {
        if ($this->projectDir === null) {
            $reflection = new ReflectionObject($this);
            $dir        = $rootDir        = dirname($reflection->getFileName());

            while (! file_exists($dir . '/composer.json')) {
                if (dirname($dir) === $dir) {
                    return $this->projectDir = $rootDir;
                }

                $dir = dirname($dir);
            }

            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path Optionally, a path to append to the app path
     *
     * @return string
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/app' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the application configuration files.
     *
     * This path is used by the configuration loader to load the application
     * configuration files. In general, you should'nt need to change this
     * value; however, you can theoretically change the path from here.
     *
     * @param string $path Optionally, a path to append to the config path
     *
     * @return string
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/config' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the database directory.
     *
     * This path is used by the migration generator and migration runner to
     * know where to place your fresh database migration classes. You're
     * free to modify the path but you probably will not ever need to.
     *
     * @param string $path Optionally, a path to append to the database path
     *
     * @return string
     */
    public function getDatabasePath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/database' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the public / web directory.
     *
     * The public path contains the assets for your web application, such as
     * your JavaScript and CSS files, and also contains the primary entry
     * point for web requests into these applications from the outside.
     *
     * @param string $path Optionally, a path to append to the public path
     *
     * @return string
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/public' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the storage directory.
     *
     * The storage path is used by Narrowspark to store cached views, logs
     * and other pieces of information.
     *
     * @param string $path Optionally, a path to append to the storage path
     *
     * @return string
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/storage' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the resources directory.
     *
     * @param string $path Optionally, a path to append to the resources path
     *
     * @return string
     */
    public function getResourcePath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/resources' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the language files.
     *
     * This path is used by the language file loader to load your application
     * language files. The purpose of these files is to store your strings
     * that are translated into other languages for views, e-mails, etc.
     *
     * @return string
     */
    public function getLangPath(): string
    {
        return $this->getResourcePath('lang');
    }

    /**
     * Get the path to the routes files.
     *
     * This path is used by the routes loader to load the application
     * routes files. In general, you should'nt need to change this
     * value; however, you can theoretically change the path from here.
     *
     * @param string $path Optionally, a path to append to the routes path
     *
     * @return string
     */
    public function getRoutesPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/routes' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function useEnvironmentPath(string $path): KernelContract
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEnvironmentFrom(string $file): KernelContract
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentPath(): string
    {
        return $this->normalizeDirectorySeparator(
            $this->environmentPath ?: $this->projectDir
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentFilePath(): string
    {
        return $this->normalizeDirectorySeparator(
            $this->getEnvironmentPath() . '/' . $this->getEnvironmentFile()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args      = $_SERVER['argv'] ?? null;
        $container = $this->getContainer();
        $env       = $container->get(EnvironmentDetector::class)->detect($callback, $args);

        $container->get(RepositoryContract::class)->set('viserio.app.env', $env);

        return $env;
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders(): void
    {
        $container = $this->getContainer();

        $container->register(new ParsersServiceProvider());
        $container->register(new EventsServiceProvider());
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new ConfigServiceProvider());
        $container->register(new LoggerServiceProvider());
        $container->register(new ConfigureLoggingServiceProvider());
        $container->register(new RoutingServiceProvider());
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings(): void
    {
        $kernel    = $this;
        $container = $this->getContainer();

        $container->singleton(EnvironmentContract::class, EnvironmentDetector::class);
        $container->singleton(KernelContract::class, function () use ($kernel) {
            return $kernel;
        });

        $container->alias(KernelContract::class, self::class);
        $container->alias(KernelContract::class, 'kernel');
        $container->alias(EnvironmentDetector::class, EnvironmentContract::class);
    }

    /**
     * Initializes the service container.
     *
     * @return void
     */
    protected function initializeContainer(): void
    {
        $this->container = new Container();
    }
}
