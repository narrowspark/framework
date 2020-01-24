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

namespace Viserio\Component\HttpFactory\Container\Provider;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class HttpFactoryServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(RequestFactoryInterface::class, RequestFactory::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
        $container->singleton(ResponseFactoryInterface::class, ResponseFactory::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
        $container->singleton(ServerRequestFactoryInterface::class, ServerRequestFactory::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
        $container->singleton(StreamFactoryInterface::class, StreamFactory::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
        $container->singleton(UploadedFileFactoryInterface::class, UploadedFileFactory::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
        $container->singleton(UriFactoryInterface::class, UriFactory::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            RequestFactory::class => RequestFactoryInterface::class,
            ResponseFactory::class => ResponseFactoryInterface::class,
            ServerRequestFactory::class => ServerRequestFactoryInterface::class,
            StreamFactory::class => StreamFactoryInterface::class,
            UploadedFileFactory::class => UploadedFileFactoryInterface::class,
            UriFactory::class => UriFactoryInterface::class,
        ];
    }
}
