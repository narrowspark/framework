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

namespace Viserio\Component\Foundation\Bootstrap;

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

        $builder->setParameter('container.dumper.preload_classes', \array_keys($preloadedClasses));
    }
}
