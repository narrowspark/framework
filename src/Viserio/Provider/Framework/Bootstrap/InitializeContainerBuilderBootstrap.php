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

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor;

class InitializeContainerBuilderBootstrap implements BootstrapContract
{
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
    public static function isSupported(KernelContract $kernel): bool
    {
        return ! $kernel->isBootstrapped();
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('viserio.container.dumper.as_files', true);
        $containerBuilder->setParameter('viserio.container.dumper.inline_class_loader', true);

        $containerBuilder->setParameter(DirectoryParameterProcessor::PARAMETER_KEY, true);

        $kernel->setContainerBuilder($containerBuilder);
    }
}
