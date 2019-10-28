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

namespace Viserio\Component\HttpFoundation\Bootstrap;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\InitializeContainerBuilderBootstrap;
use Viserio\Component\HttpFoundation\Kernel;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;

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

        $containerBuilder->singleton(ServerRequestInterface::class)
            ->setSynthetic(true);

        $containerBuilder->singleton(KernelContract::class)
            ->setSynthetic(true)
            ->setPublic(true);

        $containerBuilder->setAlias(KernelContract::class, AbstractKernel::class);
        $containerBuilder->setAlias(KernelContract::class, HttpKernelContract::class)
            ->setPublic(true);
        $containerBuilder->setAlias(KernelContract::class, Kernel::class)
            ->setPublic(true);
    }
}
