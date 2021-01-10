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

use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Console\Kernel as ConsoleKernel;
use Viserio\Component\Foundation\EnvironmentDetector;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Environment as EnvironmentContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class PrepareContainerBuilderBootstrap implements BootstrapStateContract
{
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
        return BootstrapStateContract::TYPE_AFTER;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return InitializeContainerBuilderBootstrap::class;
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

        $containerBuilder->singleton(EnvironmentContract::class)
            ->setSynthetic(true);
        $containerBuilder->setAlias(EnvironmentContract::class, EnvironmentDetector::class);

        $containerBuilder->singleton(KernelContract::class)
            ->setSynthetic(true)
            ->setPublic(true);

        $containerBuilder->setAlias(KernelContract::class, AbstractKernel::class);
        $containerBuilder->setAlias(KernelContract::class, ConsoleKernelContract::class)
            ->setPublic(true);
        $containerBuilder->setAlias(KernelContract::class, ConsoleKernel::class)
            ->setPublic(true);
    }
}
