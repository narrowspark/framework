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
    protected static $configExtensions = [
        'php',
    ];

    /**
     * Bypass given files.
     *
     * @var string[]
     */
    protected static $bypassFiles = [
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

        $config = [];

        if ($containerBuilder->hasParameter('viserio')) {
            $config = $containerBuilder->getParameter('viserio')->getValue();
        } else {
            $config['viserio'] = [];
        }

        $config['viserio']['app'] = ['env' => $env];

        foreach (static::getFiles($kernel->getConfigPath('packages'), self::$configExtensions) as $path) {
            foreach ((array) require $path as $key => $value) {
                $config[$key] = \array_merge_recursive($config[$key], $value);
            }
        }

        foreach (static::getFiles($kernel->getConfigPath(), self::$configExtensions) as $path) {
            foreach ((array) require $path as $key => $value) {
                $config[$key] = \array_merge_recursive($config[$key], $value);
            }
        }

        foreach (static::getFiles($kernel->getConfigPath($env), self::$configExtensions) as $path) {
            foreach ((array) require $path as $key => $value) {
                $config[$key] = \array_merge_recursive($config[$key], $value);
            }
        }

        foreach (static::getFiles($kernel->getConfigPath('packages' . \DIRECTORY_SEPARATOR . $env), self::$configExtensions) as $path) {
            foreach ((array) require $path as $key => $value) {
                $config[$key] = \array_merge_recursive($config[$key], $value);
            }
        }

        $containerBuilder->setParameter('viserio', $config);
    }
}
