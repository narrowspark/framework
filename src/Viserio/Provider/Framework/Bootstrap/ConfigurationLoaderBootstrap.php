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

namespace Viserio\Provider\Framework\Bootstrap;

use Viserio\Component\Foundation\Bootstrap\AbstractFilesLoaderBootstrap;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class ConfigurationLoaderBootstrap extends AbstractFilesLoaderBootstrap implements BootstrapStateContract
{
    /**
     * Supported config files.
     *
     * @var string[]
     */
    protected static array $configExtensions = [
        'php',
    ];

    /**
     * {@inheritdoc}
     */
    protected static array $bypassFiles = [
        'serviceproviders',
        'bootstrap',
    ];

    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 64;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_AFTER;
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
    public static function isSupported(KernelContract $kernel): bool
    {
        return ! $kernel->isBootstrapped();
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $containerBuilder = $kernel->getContainerBuilder();
        $env = $kernel->getEnvironment();

        foreach (static::getFiles($kernel->getConfigPath('packages'), self::$configExtensions) as $path) {
            self::setParameters($path, $containerBuilder);
        }

        foreach (static::getFiles($kernel->getConfigPath(), self::$configExtensions) as $path) {
            self::setParameters($path, $containerBuilder);
        }

        foreach (static::getFiles($kernel->getConfigPath($env), self::$configExtensions) as $path) {
            self::setParameters($path, $containerBuilder);
        }

        foreach (static::getFiles($kernel->getConfigPath('packages' . \DIRECTORY_SEPARATOR . $env), self::$configExtensions) as $path) {
            self::setParameters($path, $containerBuilder);
        }
    }

    /**
     * @param string $path
     *
     * @return array<int|string, mixed>
     */
    protected static function load(string $path): array
    {
        return (array) require $path;
    }

    /**
     * Load parameter into container from a given path.
     *
     * @param string                                                                                                    $path
     * @param \Viserio\Contract\Container\ContainerBuilder&\Viserio\Contract\Container\ServiceProvider\ContainerBuilder $containerBuilder
     *
     * @return void
     */
    private static function setParameters(string $path, $containerBuilder): void
    {
        foreach (static::load($path) as $key => $value) {
            if ($containerBuilder->hasParameter($key)) {
                $foundValue = $containerBuilder->getParameter($key)->getValue();

                if (\is_array($foundValue)) {
                    $value = \array_merge_recursive($foundValue, $value);
                }

                $containerBuilder->setParameter($key, $value);
            } else {
                $containerBuilder->setParameter($key, $value);
            }
        }
    }
}
