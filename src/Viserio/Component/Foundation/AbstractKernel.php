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
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\Contracts\Container\Factory as FactoryContract;

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
     * {@inheritdoc}
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

            if ($container instanceof FactoryContract) {
                $bootstrapperClass = $container->resolve($bootstrapper);
            } else {
                $bootstrapperClass = $container->get($bootstrapper);
            }

            $bootstrapperClass->bootstrap($this);

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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
    public function isRunningUnitTests(): bool
    {
        return $this->getContainer()->get(RepositoryContract::class)->get('viserio.app.env') == 'testing';
    }

    /**
     * {@inheritdoc}
     */
    public function isRunningInConsole(): bool
    {
        return php_sapi_name() == 'cli' || php_sapi_name() == 'phpdbg';
    }

    /**
     * {@inheritdoc}
     */
    public function isDownForMaintenance(): bool
    {
        return file_exists($this->getStoragePath('framework/down'));
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/app' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/config' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/database' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/public' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/storage' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcePath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/resources' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLangPath(): string
    {
        return $this->getResourcePath('lang');
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesPath(): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/routes'
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
        $env       = $container->get(EnvironmentContract::class)->detect($callback, $args);

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
