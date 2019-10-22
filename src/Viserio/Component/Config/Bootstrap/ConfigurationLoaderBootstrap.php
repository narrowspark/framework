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

namespace Viserio\Component\Config\Bootstrap;

use Viserio\Component\Foundation\Bootstrap\AbstractFilesLoaderBootstrap;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

// @todo change this bootstrap to a after container, with warmup
class ConfigurationLoaderBootstrap implements BootstrapStateContract
{
    /**
     * Supported config files.
     *
     * @var string[]
     */
    protected static $configExtensions = [
        'php',
        'xml',
        'yaml',
        'yml',
        'toml',
    ];

    /**
     * Bypass given files.
     *
     * @var string[]
     */
    protected static $bypassFiles = [
        'serviceproviders',
        'staticalproxy',
        'bootstrap',
    ];

    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 32;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_BEFORE;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return LoadServiceProviderBootstrap::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $containerBuilder = $kernel->getContainerBuilder();
        $containerBuilder->register(new class($kernel, static::$configExtensions, static::$bypassFiles) extends AbstractFilesLoaderBootstrap implements ExtendServiceProviderContract {
            /** @var \Viserio\Contract\Foundation\Kernel */
            private $kernel;

            /** @var array */
            private $extensions;

            /**
             * @param \Viserio\Contract\Foundation\Kernel $kernel
             * @param array                               $extensions
             * @param array                               $bypass
             */
            public function __construct(KernelContract $kernel, array $extensions, array $bypass)
            {
                $this->kernel = $kernel;
                $this->extensions = $extensions;
                self::$bypassFiles = $bypass;
            }

            /**
             * {@inheritdoc}
             */
            public function getExtensions(): array
            {
                return [
                    RepositoryContract::class => function (ObjectDefinitionContract $configDefinition): void {
                        $loadedFromCache = false;

                        // First we will see if we have a cache configuration file.
                        // If we do, we'll load the configuration items.
                        if (\file_exists($cached = $this->kernel->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'config.cache.php'))) {
                            $items = require $cached;

                            $configDefinition->addMethodCall('setArray', [$items, true]);

                            $loadedFromCache = true;
                        }

                        // Next we will spin through all of the configuration files in the configuration
                        // directory and load each one into the config manager.
                        if (! $loadedFromCache) {
                            $configDefinition->addMethodCall('set', ['viserio.app.env', $this->kernel->getEnvironment()]);

                            foreach (static::getFiles($this->kernel->getConfigPath('packages'), $this->extensions) as $path) {
                                $configDefinition->addMethodCall('import', [$path]);
                            }

                            foreach (static::getFiles($this->kernel->getConfigPath(), $this->extensions) as $path) {
                                $configDefinition->addMethodCall('import', [$path]);
                            }

                            foreach (static::getFiles($this->kernel->getConfigPath($this->kernel->getEnvironment()), $this->extensions) as $path) {
                                $configDefinition->addMethodCall('import', [$path]);
                            }

                            foreach (static::getFiles($this->kernel->getConfigPath('packages' . \DIRECTORY_SEPARATOR . $this->kernel->getEnvironment()), $this->extensions) as $path) {
                                $configDefinition->addMethodCall('import', [$path]);
                            }
                        }
                    },
                ];
            }
        });
    }
}
