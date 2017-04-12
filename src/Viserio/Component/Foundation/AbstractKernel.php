<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use ReflectionObject;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
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

abstract class AbstractKernel implements
    HttpKernelContract,
    KernelContract,
    TerminableContract,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract
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
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    private $hasBeenBootstrapped = false;

    /**
     * Project path.
     *
     * @var string
     */
    private $projectDir;

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    private $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     *
     * @var string
     */
    private $environmentPath;

    /**
     * Create a new application instance.
     *
     * Let's start make magic!
     */
    public function __construct()
    {
        $this->projectDir = $this->getProjectDir();

        $this->registerBaseServiceProviders();

        $this->registerBaseBindings();
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'middlewares'    => [
                'skip' => false,
            ],
            'locale'         => 'en',
            'fallback_locale'=> 'en',
        ];
    }

    /**
     * Set a container instance.
     *
     * @param \Viserio\Component\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(ContainerContract $container): self
    {
        $this->container = $container;

        return $this;
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
        $this->hasBeenBootstrapped = true;

        foreach ($bootstrappers as $bootstrapper) {
            $this->get(EventManagerContract::class)->trigger(new BootstrappingEvent($bootstrapper, $this));

            $this->make($bootstrapper)->bootstrap($this);

            $this->get(EventManagerContract::class)->trigger(new BootstrappedEvent($bootstrapper, $this));
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
        return $this->options['locale'];
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): self
    {
        $this->get(RepositoryContract::class)->set('viserio.app.locale', $locale);

        if ($this->has(TranslationManager::class)) {
            $this->get(TranslationManager::class)->setLocale($locale);
        }

        $this->get(EventManagerContract::class)->trigger(new LocaleChangedEvent($this, $locale));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLocale(): string
    {
        return $this->options['fallback_locale'];
    }

    /**
     * {@inheritdoc}
     */
    public function isLocal(): bool
    {
        return $this->get(RepositoryContract::class)->get('viserio.app.env') == 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function runningUnitTests(): bool
    {
        return $this->get(RepositoryContract::class)->get('viserio.app.env') == 'testing';
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
        return file_exists($this->storagePath('framework/down'));
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
     * Get the path to the bootstrap directory.
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     *
     * @return string
     */
    public function getBootstrapPath(string $path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->projectDir . '/bootstrap' . ($path ? '/' . $path : $path)
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
            ($this->storagePath ?: $this->projectDir . '/storage') . ($path ? '/' . $path : $path)
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
    public function getEnvironmentPath(): string
    {
        return $this->normalizeDirectorySeparator(
            $this->environmentPath ?: $this->projectDir
        );
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
        return $this->normalizeDirectorySeparator(
            $this->environmentPath() . '/' . $this->environmentFile()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = $_SERVER['argv'] ?? null;
        $env  = $this->get(EnvironmentDetector::class)->detect($callback, $args);

        $this->get(RepositoryContract::class)->set('viserio.app.env', $env);

        return $env;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders(): void
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
     *
     * @return void
     */
    protected function registerBaseBindings(): void
    {
        $kernel = $this;

        $this->singleton(EnvironmentDetector::class, EnvironmentDetector::class);
        $this->singleton(KernelContract::class, function () use ($kernel) {
            return $kernel;
        });

        $this->alias(KernelContract::class, self::class);
        $this->alias(KernelContract::class, 'kernel');
    }
}
