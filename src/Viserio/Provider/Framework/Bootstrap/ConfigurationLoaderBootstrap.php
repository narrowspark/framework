<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
     * @return array<int|string, mixed>
     */
    protected static function load(string $path): array
    {
        return (array) require $path;
    }

    /**
     * Load parameter into container from a given path.
     *
     * @param \Viserio\Contract\Container\ContainerBuilder&\Viserio\Contract\Container\ServiceProvider\ContainerBuilder $containerBuilder
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
