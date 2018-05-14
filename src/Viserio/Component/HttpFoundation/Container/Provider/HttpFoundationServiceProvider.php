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

use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\HttpFoundation\Kernel;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Foundation\Kernel as ContractKernel;
use Viserio\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;

class HttpFoundationServiceProvider implements AliasServiceProviderContract, ExtendServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        if (\interface_exists(ContextProviderInterface::class)) {
            $container->singleton(ContextProviderInterface::class, SourceContextProvider::class)
                ->setArguments([
                    (new ReferenceDefinition(ContractKernel::class))
                        ->addMethodCall('getCharset'),
                    (new ReferenceDefinition(ContractKernel::class))
                        ->addMethodCall('getRootDir'),
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            DispatcherContract::class => static function (ObjectDefinitionContract $definition): void {
                if ($definition->getValue() instanceof MiddlewareAwareContract) {
                    $definition->addMethodCall('setMiddlewarePriorities', [new OptionDefinition('middleware_priority', Kernel::class)]);
                    $definition->addMethodCall('withMiddleware', [new OptionDefinition('route_middleware', Kernel::class)]);
                    $definition->addMethodCall('setMiddlewareGroups', [new OptionDefinition('middleware_groups', Kernel::class)]);
                }
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        if (\interface_exists(ContextProviderInterface::class)) {
            return [
                SourceContextProvider::class => ContextProviderInterface::class,
            ];
        }

        return [];
    }
}
