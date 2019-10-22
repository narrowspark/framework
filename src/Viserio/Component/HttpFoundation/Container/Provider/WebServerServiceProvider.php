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

namespace Viserio\Component\HttpFoundation\Container\Provider;

use Cake\Chronos\Chronos;
use Narrowspark\HttpStatus\HttpStatus;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\HttpFoundation\Console\Command\UpCommand;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class WebServerServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        if (\class_exists(Chronos::class) && \class_exists(HttpStatus::class)) {
            $container->singleton(DownCommand::class)
                ->addTag('console.command');
            $container->singleton(UpCommand::class)
                ->addTag('console.command');
        }
    }
}
