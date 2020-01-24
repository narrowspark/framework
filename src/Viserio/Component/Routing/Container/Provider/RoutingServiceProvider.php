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

namespace Viserio\Component\Routing\Container\Provider;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Dispatcher\SimpleDispatcher;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Router;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Contract\Routing\Router as RouterContract;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

class RoutingServiceProvider implements AliasServiceProviderContract,
    ExtendServiceProviderContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(
            DispatcherContract::class,
            \class_exists(Pipeline::class) ? MiddlewareBasedDispatcher::class : SimpleDispatcher::class
        )
            ->addTag(ResolvePreloadPipe::TAG)
            ->addMethodCall('setEventManager', [new ReferenceDefinition(EventManagerContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->setPublic(true);

        $container->singleton(RouterContract::class, Router::class)
            ->addTag(ResolvePreloadPipe::TAG)
            ->addMethodCall('setContainer')
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            UriFactoryInterface::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): ObjectDefinitionContract {
                $container->singleton(UrlGeneratorContract::class, UrlGenerator::class)
                    ->setArguments([
                        (new ReferenceDefinition(RouterContract::class))->addMethodCall('getRoutes'),
                        new ReferenceDefinition(ServerRequestInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE),
                        new ReferenceDefinition(UriFactoryInterface::class),
                    ]);

                $container->setAlias(UrlGeneratorContract::class, UrlGenerator::class);

                return $definition;
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            'route' => RouterContract::class,
            'router' => RouterContract::class,
            Router::class => RouterContract::class,
        ];
    }
}
