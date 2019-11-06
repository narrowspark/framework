<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation;

use Closure;
use DateTimeImmutable;
use ProxyManager\Configuration;
use Psr\Container\ContainerInterface;
use ReflectionObject;
use Viserio\Component\Container\AbstractCompiledContainer;
use Viserio\Component\Container\LazyProxy\ProxyDumper;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\LazyProxy\Dumper as ProxyDumperContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Environment as EnvironmentContract;
use Viserio\Contract\Foundation\Exception\RuntimeException;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

abstract class AbstractKernel implements KernelContract,
    ProvidesDefaultOptionContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
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

    /** @var string */
    public const END_OF_MAINTENANCE = '?';

    /** @var string */
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
     * A Container instance.
     *
     * @var null|\Viserio\Contract\Container\CompiledContainer
     */
    protected $container;

    /**
     * A Container Builder instance.
     *
     * @var null|\Viserio\Contract\Container\ContainerBuilder & \Viserio\Contract\Container\ServiceProvider\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * A EnvironmentDetector instance.
     *
     * @var \Viserio\Contract\Foundation\Environment
     */
    protected $environmentDetector;

    /**
     * The bootstrap manager instance.
     *
     * @var \Viserio\Component\Foundation\BootstrapManager
     */
    protected $bootstrapManager;

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
        $this->rootDir = $this->getRootDir();
        $this->projectDirs = $this->initProjectDirs();

        $this->environmentDetector = new EnvironmentDetector();
        $this->bootstrapManager = new BootstrapManager($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container): KernelContract
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerBuilder(): ContainerBuilderContract
    {
        return $this->containerBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainerBuilder($containerBuilder): KernelContract
    {
        $this->containerBuilder = $containerBuilder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentDetector(): EnvironmentContract
    {
        return $this->environmentDetector;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir(): string
    {
        if ($this->rootDir === null) {
            $reflection = new ReflectionObject($this);
            $dir = $rootDir = \dirname($reflection->getFileName());

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
    public function getProxyDumper(): ?ProxyDumperContract
    {
        if (class_exists(Configuration::class)) {
            return new ProxyDumper();
        }

        return null;
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
            'timezone' => 'UTC',
            'charset' => 'UTF-8',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerBaseClass(): string
    {
        return AbstractCompiledContainer::class;
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
    public static function getOptionValidators(): array
    {
        // @todo create better validator with messages how to fix this
        return [
            'env' => ['string'],
            'debug' => ['bool'],
            'timezone' => ['string'],
            'charset' => ['string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setKernelConfigurations($config): void
    {
        $this->resolvedOptions = self::resolveOptions($config);

        \date_default_timezone_set($this->resolvedOptions['timezone']);

        if (\function_exists('mb_internal_encoding')) {
            \mb_internal_encoding($this->resolvedOptions['charset']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCharset(): string
    {
        return $this->resolvedOptions['charset'];
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
    public function isBootstrapped(): bool
    {
        return \file_exists(\rtrim($this->getBootstrapDirPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $this->getBootstrapLockFileName());
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
    public function getTestsPath(string $path = ''): string
    {
        return $this->projectDirs['tests-dir'] . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
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
        $env = $this->environmentDetector->detect($callback, $args);

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
     * {@inheritdoc}
     */
    public function detectDebugMode(Closure $callback): bool
    {
        return $this->resolvedOptions['debug'] = $callback();
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisteredServiceProviders(): array
    {
        $providersPath = $this->getConfigPath('serviceproviders.php');

        $providers = [];

        if (\file_exists($providersPath)) {
            $providers = (array) require $providersPath;
        }

        if (\file_exists($providersEnvPath = $this->getConfigPath($this->getEnvironment() . \DIRECTORY_SEPARATOR . 'serviceproviders.php'))) {
            $providers = \array_merge($providers, (array) require $providersEnvPath);
        }

        return $providers;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap(): void
    {
        if (! $this->bootstrapManager->hasBeenBootstrapped()) {
            $bootstraps = [];

            foreach ($this->getPreparedBootstraps() as $classes) {
                /** @var \Viserio\Contract\Foundation\BootstrapState $class */
                foreach ($classes as $class) {
                    if (\in_array(BootstrapStateContract::class, \class_implements($class), true)) {
                        $method = 'add' . $class::getType() . 'Bootstrapping';

                        $this->bootstrapManager->{$method}($class::getBootstrapper(), [$class, 'bootstrap']);
                    } else {
                        $bootstraps[] = $class;
                    }
                }
            }

            $this->bootstrapManager->bootstrapWith($bootstraps);

            if (! \is_dir($concurrentDirectory = $this->getBootstrapDirPath()) && ! \mkdir($concurrentDirectory, 0777, true) && ! \is_dir($concurrentDirectory)) {
                throw new RuntimeException(\sprintf('Foundation cache directory does not exist and cannot be created: %s.', $concurrentDirectory));
            }

            \file_put_contents($this->getBootstrapDirPath() . \DIRECTORY_SEPARATOR . $this->getBootstrapLockFileName(), (new DateTimeImmutable())->format(DateTimeImmutable::ATOM));
        }
    }

    /**
     * Returns the bootstrap lock file path.
     *
     * @return string
     */
    abstract protected function getBootstrapLockFileName(): string;

    /**
     * Returns the bootstrap lock file path.
     *
     * @return string
     */
    protected function getBootstrapDirPath(): string
    {
        return $this->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'foundation' . \DIRECTORY_SEPARATOR . $this->getEnvironment());
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
            $dirs = [
                'app-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'app',
                'config-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'config',
                'database-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'database',
                'public-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'public',
                'resources-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'resources',
                'routes-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'routes',
                'tests-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'tests',
                'storage-dir' => $this->rootDir . \DIRECTORY_SEPARATOR . 'storage',
            ];

            if (\file_exists($jsonFile)) {
                $jsonData = \json_decode(\file_get_contents($jsonFile), true);
                $extra = $jsonData['extra'] ?? [];

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
     * Returns prepared bootstrap classes, sorted and filtered after static::$allowedBootstrapTypes.
     *
     * @return array
     */
    protected function getPreparedBootstraps(): array
    {
        $preparedBootstraps = [];

        /** @var \Viserio\Contract\Foundation\Bootstrap $class */
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
