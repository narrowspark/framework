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

namespace Viserio\Component\Exception\Container\Provider;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\SymfonyDisplayer;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsJsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\ContentTypeFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Http\Handler;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Contract\View\Factory as FactoryContract;
use Whoops\Run;

class HttpExceptionServiceProvider implements AliasServiceProviderContract,
    ExtendServiceProviderContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $configReference = new ReferenceDefinition('config');
        $responseFactoryInterfaceReference = new ReferenceDefinition(ResponseFactoryInterface::class);

        $container->singleton(HtmlDisplayer::class)
            ->setArguments([
                $responseFactoryInterfaceReference,
                $configReference,
            ]);
        $container->singleton(JsonDisplayer::class);
        $container->singleton(JsonApiDisplayer::class);
        $container->singleton(SymfonyDisplayer::class)
            ->setArguments([new ReferenceDefinition(ResponseFactoryInterface::class), new ReferenceDefinition('config')]);

        $container->singleton(VerboseFilter::class)
            ->addArgument($configReference);
        $container->singleton(ContentTypeFilter::class);
        $container->singleton(CanDisplayFilter::class);

        $container->singleton(HttpHandlerContract::class, Handler::class)
            ->setArguments([
                $configReference,
                $responseFactoryInterfaceReference,
                new ReferenceDefinition(LoggerInterface::class, ReferenceDefinition::NULL_ON_INVALID_REFERENCE),
            ])
            ->setMethodCalls([
                ['addFilter', [new ReferenceDefinition(VerboseFilter::class), 32]],
                ['addFilter', [new ReferenceDefinition(CanDisplayFilter::class), 64]],
                ['addFilter', [new ReferenceDefinition(ContentTypeFilter::class), 128]],
                ['addDisplayer', [new ReferenceDefinition(SymfonyDisplayer::class), 64]],
                ['addDisplayer', [new ReferenceDefinition(HtmlDisplayer::class), 256]],
                ['addDisplayer', [new ReferenceDefinition(JsonDisplayer::class), 1024]],
                ['addDisplayer', [new ReferenceDefinition(JsonApiDisplayer::class), 2048]],
            ])
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            HttpHandlerContract::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): void {
                if ($container->has(FactoryContract::class)) {
                    $container->singleton(ViewDisplayer::class);

                    $definition->addMethodCall('addDisplayer', [new ReferenceDefinition(ViewDisplayer::class), 128]);
                }

                if (\class_exists(Run::class)) {
                    $container->singleton(WhoopsPrettyDisplayer::class)
                        ->setArguments([new ReferenceDefinition(ResponseFactoryInterface::class), new ReferenceDefinition('config')]);
                    $container->singleton(WhoopsJsonDisplayer::class);

                    $definition->addMethodCall('addDisplayer', [new ReferenceDefinition(WhoopsPrettyDisplayer::class), 32]);
                    $definition->addMethodCall('addDisplayer', [new ReferenceDefinition(WhoopsJsonDisplayer::class), 512]);
                }
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Handler::class => HttpHandlerContract::class,
        ];
    }
}
