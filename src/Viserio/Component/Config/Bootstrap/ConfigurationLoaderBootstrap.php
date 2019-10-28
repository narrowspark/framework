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
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class ConfigurationLoaderBootstrap extends AbstractFilesLoaderBootstrap implements BootstrapStateContract
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

        $configDefinition = $containerBuilder->findDefinition(RepositoryContract::class);

        $configDefinition->addMethodCall('set', ['viserio.app.env', $kernel->getEnvironment()]);

        foreach (static::getFiles($kernel->getConfigPath('packages'), self::$configExtensions) as $path) {
            $configDefinition->addMethodCall('import', [$path]);
        }

        foreach (static::getFiles($kernel->getConfigPath(), self::$configExtensions) as $path) {
            $configDefinition->addMethodCall('import', [$path]);
        }

        foreach (static::getFiles($kernel->getConfigPath($kernel->getEnvironment()), self::$configExtensions) as $path) {
            $configDefinition->addMethodCall('import', [$path]);
        }

        foreach (static::getFiles($kernel->getConfigPath('packages' . \DIRECTORY_SEPARATOR . $kernel->getEnvironment()), self::$configExtensions) as $path) {
            $configDefinition->addMethodCall('import', [$path]);
        }
    }
}
