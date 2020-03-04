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

namespace Viserio\Component\Filesystem\Container\Provider;

use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Filesystem\DirectorySystem as DirectorySystemContract;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contract\Filesystem\LinkSystem as LinkSystemContract;

class FilesystemServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(FilesystemContract::class, Filesystem::class)
            ->addTag(ResolvePreloadPipe::TAG);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            'files' => FilesystemContract::class,
            Filesystem::class => FilesystemContract::class,
            DirectorySystemContract::class => FilesystemContract::class,
            LinkSystemContract::class => FilesystemContract::class,
        ];
    }
}
