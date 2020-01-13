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

namespace Viserio\Bridge\Dotenv\Container\Provider;

use Viserio\Bridge\Dotenv\Processor\EnvParameterProcessor;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class DotenvServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(EnvParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
    }
}
