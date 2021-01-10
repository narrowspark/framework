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

use Viserio\Contract\Container\ServiceProvider\PreloadServiceProvider as PreloadServiceProviderContract;
use Viserio\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class LoadServiceProviderBootstrap implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 128;
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
        $builder = $kernel->getContainerBuilder();

        $preloadedClasses = [];

        foreach ($kernel->getRegisteredServiceProviders() as $serviceProvider) {
            $serviceProviderInstance = new $serviceProvider();

            $builder->register($serviceProviderInstance);

            if ($serviceProviderInstance instanceof PreloadServiceProviderContract) {
                foreach ($serviceProviderInstance->getClassesToPreload() as $class) {
                    $preloadedClasses[$class] = true;
                }
            }
        }

        $builder->setParameter('viserio.container.dumper.preload_classes', \array_keys($preloadedClasses));
    }
}
