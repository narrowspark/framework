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

use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class ConfigureKernelBootstrap implements BootstrapStateContract
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
        return InitializeContainerBootstrap::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported(KernelContract $kernel): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        $timezone = 'UTC';

        if ($container->hasParameter('viserio.app.timezone')) {
            $timezone = $container->getParameter('viserio.app.timezone');
        }

        \date_default_timezone_set($timezone);

        $charset = 'UTF-8';

        if ($container->hasParameter('viserio.app.charset')) {
            $charset = $container->getParameter('viserio.app.charset');
        }

        if (\function_exists('mb_internal_encoding')) {
            \mb_internal_encoding($charset);
        }
    }
}
