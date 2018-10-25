<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use ReflectionObject;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

abstract class AbstractKernel implements
    KernelContract,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * The current framework full version.
     *
     * @var string
     */
    public const VERSION = '1.0.0-DEV';

    /**
     * The current framework version id.
     *
     * @var int
     */
    public const VERSION_ID = 10000;

    /**
     * The current framework "major" version.
     *
     * @var int
     */
    public const MAJOR_VERSION = 1;

    /**
     * The current framework "minor" version.
     *
     * @var int
     */
    public const MINOR_VERSION = 0;

    /**
     * The current framework "release" version.
     *
     * @var int
     */
    public const RELEASE_VERSION = 0;

    /**
     * The current framework "extra" version.
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
     * List of allowed bootstrap types.
     *
     * @internal
     *
     * @var array
     */
    protected static $allowedBootstrapTypes = ['global'];

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

        $this->container = $this->initializeContainer();
        $this->registerBaseBindings();
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
    public function getRootDir(): string
    {
        if ($this->rootDir === null) {
            $reflection = new ReflectionObject($this);
            $dir        = $rootDir = \dirname($reflection->getFileName());

            while (! \file_exists($dir . \DIRECTORY_SEPARATOR . 'composer.json')) {
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
    public function getEnvironmentFile(): string
    {
        return $this->environmentFile ?: '.env';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentPath(): string
    {
        return $this->environmentPath ?: $this->rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
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
    public static function getMandatoryOptions(): array
    {
        return [
            'env',
            'debug',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setKernelConfigurations($config): void
    {
        $this->resolvedOptions = self::resolveOptions($config);

        \date_default_timezone_set($this->resolvedOptions['viserio']['app']['timezone'] ?? 'UTC');
        \mb_internal_encoding('UTF-8');
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
        return $this->resolvedOptions['env'] === 'local';
    }

    /**
     * {@inheritdoc}
     */
    public function isRunningUnitTests(): bool
    {
        return $this->resolvedOptions['env'] === 'testing';
    }

    /**
     * {@inheritdoc}
     */
    public function isRunningInConsole(): bool
    {
        return \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function isDownForMaintenance(): bool
    {
        return \file_exists($this->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'down'));
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->projectDirs['app-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->projectDirs['config-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePath(string $path = ''): string
    {
        return $this->projectDirs['database-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->projectDirs['public-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->projectDirs['storage-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcePath(string $path = ''): string
    {
        return $this->projectDirs['resources-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
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
    public function getRoutesPath(string $path = ''): string
    {
        return $this->projectDirs['routes-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
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
    public function getEnvironmentFilePath(): string
    {
        return $this->getEnvironmentPath() . \DIRECTORY_SEPARATOR . $this->getEnvironmentFile();
    }

    /**
     * {@inheritdoc}
     */
    public function detectEnvironment(Closure $callback): string
    {
        $args = $_SERVER['argv'] ?? null;
        $env  = $this->getContainer()->get(EnvironmentContract::class)->detect($callback, $args);

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

        $providersEnvPath = $this->getConfigPath($this->getEnvironment() . \DIRECTORY_SEPARATOR . 'serviceproviders.php');

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
            $jsonFile = $this->rootDir . \DIRECTORY_SEPARATOR . 'composer.json';
            $dirs     = [
                'app-dir'       => $this->rootDir . \DIRECTORY_SEPARATOR . 'app',
                'config-dir'    => $this->rootDir . \DIRECTORY_SEPARATOR . 'config',
                'database-dir'  => $this->rootDir . \DIRECTORY_SEPARATOR . 'database',
                'public-dir'    => $this->rootDir . \DIRECTORY_SEPARATOR . 'public',
                'resources-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'resources',
                'routes-dir'    => $this->rootDir . \DIRECTORY_SEPARATOR . 'routes',
                'tests-dir'     => $this->rootDir . \DIRECTORY_SEPARATOR . 'tests',
                'storage-dir'   => $this->rootDir . \DIRECTORY_SEPARATOR . 'storage',
            ];

            if (\file_exists($jsonFile)) {
                $jsonData = \json_decode(\file_get_contents($jsonFile), true);
                $extra    = $jsonData['extra'] ?? [];

                foreach ($extra as $key => $value) {
                    if (\array_key_exists($key, $dirs)) {
                        $dirs[$key] = $this->rootDir . \DIRECTORY_SEPARATOR . \ltrim($value, '/\\');
                    }
                }
            }

            $this->projectDirs = $dirs;
        }

        return $this->projectDirs;
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
     * @return \Viserio\Component\Contract\Container\Container
     */
    protected function initializeContainer(): ContainerContract
    {
        return new Container();
    }

    /**
     * Returns prepared bootstrap classes, sorted and filtered after static::$allowedBootstrapTypes.
     *
     * @return array
     */
    protected function getPreparedBootstraps(): array
    {
        $preparedBootstraps = [];

        /** @var \Viserio\Component\Contract\Foundation\Bootstrap $class */
        foreach ((array) require $this->getConfigPath('bootstrap.php') as $class => $data) {
            foreach ((array) $data as $type) {
                if (\in_array($type, static::$allowedBootstrapTypes, true)) {
                    $preparedBootstraps[$class::getPriority()][] = $class;
                }
            }
        }

        \ksort($preparedBootstraps);

        return $preparedBootstraps;
    }
}
