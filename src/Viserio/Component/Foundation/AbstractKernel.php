<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use ReflectionObject;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Events\Provider\EventsServiceProvider;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\Routing\Provider\RoutingServiceProvider;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

abstract class AbstractKernel implements
    KernelContract,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use NormalizePathAndDirectorySeparatorTrait;
    use OptionsResolverTrait;

    /**
     * The current Framework full version.
     *
     * @var string
     */
    public const VERSION = '1.0.0-DEV';

    /**
     * The current Framework version id.
     *
     * @var int
     */
    public const VERSION_ID  = 10000;

    /**
     * The current Framework "major" version.
     *
     * @var int
     */
    public const MAJOR_VERSION = 1;

    /**
     * The current Framework "minor" version.
     *
     * @var int
     */
    public const MINOR_VERSION = 0;

    /**
     * The current Framework "release" version.
     *
     * @var int
     */
    public const RELEASE_VERSION = 0;

    /**
     * The current Framework "extra" version
     *
     * @var string
     */
    public const EXTRA_VERSION = 'DEV';

    /**
     * @var string
     */
    public const END_OF_MAINTENANCE = '?';

    /**
     * @var string
     */
    public const END_OF_LIFE = '?';

    /**
     * Container instance.
     *
     * @var \Viserio\Component\Contracts\Container\Container|null
     */
    protected $container;

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
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Create a new kernel instance.
     *
     * Let's start! Making magic!
     */
    public function __construct()
    {
        $this->projectDir = $this->getProjectDir();

        $this->initializeContainer();
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
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
            'locale'          => 'en',
            'fallback_locale' => 'en',
            'aliases'         => [],
            'timezone'        => 'UTC',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'env',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ?ContainerContract
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function setKernelConfigurations($data): void
    {
        $this->resolvedOptions = $this->resolveOptions($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getKernelConfigurations(): array
    {
        return $this->resolvedOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocal(): bool
    {
        return $this->resolvedOptions['env'] == 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function isRunningUnitTests(): bool
    {
        return $this->resolvedOptions['env'] == 'testing';
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

        if ($container->has(RepositoryContract::class)) {
            $container->get(RepositoryContract::class)->set('viserio.app.env', $env);
        }

        $this->resolvedOptions['env'] = $env;

        return $env;
    }

    /**
     * Register all of the application / kernel service providers.
     *
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     *
     * @return array
     */
    protected function registerServiceProviders(KernelContract $kernel): array
    {
        $providers = $kernel->getConfigPath('/serviceproviders.php');

        if (file_exists($providers)) {
            return require_once $providers;
        }

        return [];
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders(): void
    {
        $container = $this->getContainer();

        $container->register(new EventsServiceProvider());
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
        $container->singleton(BootstrapManager::class, BootstrapManager::class);

        $container->alias(KernelContract::class, self::class);
        $container->alias(KernelContract::class, 'kernel');
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

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }
}
