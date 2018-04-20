<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use ReflectionObject;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Log\Provider\LoggerServiceProvider;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
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
    public const VERSION_ID = 10000;

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
     * The current Framework "extra" version.
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
     * @var \Viserio\Component\Contract\Container\Container
     */
    protected $container;

    /**
     * Project root path.
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Project root dirs.
     *
     * @var string
     */
    protected $projectDirs;

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
        $this->rootDir     = $this->getRootDir();
        $this->projectDirs = $this->initProjectDirs();

        $this->initializeContainer();
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
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
    public static function getMandatoryOptions(): iterable
    {
        return [
            'env',
            'debug',
        ];
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
    public function setKernelConfigurations($data): void
    {
        $this->resolvedOptions = self::resolveOptions($data);
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
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    /**
     * {@inheritdoc}
     */
    public function isDownForMaintenance(): bool
    {
        return \file_exists($this->getStoragePath('framework/down'));
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir(): string
    {
        if ($this->rootDir === null) {
            $reflection = new ReflectionObject($this);
            $dir        = $rootDir = \dirname($reflection->getFileName());

            while (! \file_exists($dir . '/composer.json')) {
                if (\dirname($dir) === $dir) {
                    return $this->rootDir = $rootDir;
                }

                $dir = \dirname($dir);
            }

            $this->rootDir = $dir;
        }

        return $this->rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return self::normalizeDirectorySeparator(
            $this->projectDirs['app-dir'] . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return self::normalizeDirectorySeparator(
            $this->projectDirs['config-dir'] . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePath(string $path = ''): string
    {
        return self::normalizeDirectorySeparator(
            $this->projectDirs['database-dir'] . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return self::normalizeDirectorySeparator(
            $this->projectDirs['public-dir'] . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return self::normalizeDirectorySeparator(
            $this->projectDirs['storage-dir'] . ($path ? '/' . $path : $path)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcePath(string $path = ''): string
    {
        return self::normalizeDirectorySeparator(
            $this->projectDirs['resources-dir'] . ($path ? '/' . $path : $path)
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
        return self::normalizeDirectorySeparator($this->projectDirs['routes-dir']);
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
        return self::normalizeDirectorySeparator(
            $this->environmentPath ?: $this->rootDir
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
        return self::normalizeDirectorySeparator(
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
     * {@inheritdoc}
     */
    public function getEnvironment(): string
    {
        return $this->resolvedOptions['env'];
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug(): bool
    {
        return $this->resolvedOptions['debug'];
    }

    /**
     * Register all of the application / kernel service providers.
     *
     * @return array
     */
    public function registerServiceProviders(): array
    {
        $providersPath = $this->getConfigPath('serviceproviders.php');

        $providers = [];

        if (\file_exists($providersPath)) {
            $providers = (array) require $providersPath;
        }

        $providersEnvPath = $this->getConfigPath($this->getEnvironment() . '/serviceproviders.php');

        if (\file_exists($providersEnvPath)) {
            $providers = \array_merge($providers, (array) require $providersEnvPath);
        }

        return $providers;
    }

    /**
     * Merge composer project dir settings with the default narrowspark dir settings.
     *
     * @return array
     */
    protected function initProjectDirs(): array
    {
        if ($this->projectDirs === null) {
            $jsonFile = $this->rootDir . '/composer.json';
            $dirs     = [
                'app-dir'       => $this->rootDir . '/app',
                'config-dir'    => $this->rootDir . '/config',
                'database-dir'  => $this->rootDir . '/database',
                'public-dir'    => $this->rootDir . '/public',
                'resources-dir' => $this->rootDir . '/resources',
                'routes-dir'    => $this->rootDir . '/routes',
                'tests-dir'     => $this->rootDir . '/tests',
                'storage-dir'   => $this->rootDir . '/storage',
            ];

            if (\file_exists($jsonFile)) {
                $jsonData = \json_decode(\file_get_contents($jsonFile), true);
                $extra    = $jsonData['extra'] ?? [];

                foreach ($extra as $key => $value) {
                    if (\array_key_exists($key, $dirs)) {
                        $dirs[$key] = $this->rootDir . '/' . \ltrim($value, '/\\');
                    }
                }
            }

            $this->projectDirs = $dirs;
        }

        return $this->projectDirs;
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders(): void
    {
        $container = $this->getContainer();

        $container->register(new LoggerServiceProvider());
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
}
